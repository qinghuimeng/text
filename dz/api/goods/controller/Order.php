<?php
namespace api\goods\controller;

use think\Controller;
use \think\Model;
use \think\Db;
use \think\Config;

use api\goods\model\DoOrder;


class Order extends Controller
{

	public $re;
 	public function _initialize(){ 
    	$re = new DoOrder();
    	$this -> re = $re;
    }

    //查看订单列表
    public function listOrder(){
    	$uid = input('uid');
    	$type = input('type_')?input('type_'):0;//1，待支付；2：待发货；3：待收货；4：交易完成
    	$page = input('page')?input('page'):0;//0为第一页  
    	$list = $this -> re -> doListOrder($uid,$type,$page);
    	// return $list;exit;
    	if($list){
    		return $list;
    	}else{
    		return -1;
    	}
    }

    //取消订单
    public function xiaoOrder(){
    	$order_sn = input('sn');
    	// return $order_sn;exit;
    	$re = $this -> re -> doXiaoOrder($order_sn);
    	return $re;
    }

    //确认订单
    public function okOrder(){
    	$order_sn = input('sn');
    	// return $order_sn;exit;
    	$re = $this -> re -> doOkOrder($order_sn);
    	return $re;
    }

    //用户删除订单（失效）
    public function shiXiaoOrder(){
    	$order_sn = input('sn');
    	// return $order_sn;exit;
    	$re = $this -> re -> doShiXiaoOrder($order_sn);
    	return $re;
    }

    //查看确认订单信息(支付时看)
    public function lookOrder(){
    	$data['uid'] = input('uid');
    	$data['goods'] = object_to_array(json_decode(input('goods')));
    	// return $data['goods'] ;
    	$res = $this -> re -> doLookOrder($data);

    	return $res;

    }

    //订单里查询订单信息
    public function showOrder(){
    	$sn = input('sn');
    	$list = $this-> re -> doShowOrder($sn);
    	return $list;
    }

    //订单里支付
     public function payOrder(){


		$con['sn'] =  input('sn');
		$con['out_trade_no'] = uniqid();
		// $con['out_trade_no'] = $con['out_trade_no'].uniqid();
		$order = $this-> re ->doPayOrder($con['sn']);
		$con['title'] = $order['title'];
     	$con['price'] = $order['order_amount']*100;
     	
		$con['cid']=input('cid');
		$con['code'] = input('code');
		$con['uid'] = input('uid');
		$con['shipping_fee']='0.00';//运费暂时不管
		$con['uniqid'] = uniqid();
		$b = $this -> re -> order_pay($con);
		$res = $this -> weixin($con);
			return $res ;
     }

	//下单 + 支付接口
    public function indexcall(){
		$con = array();
    	$address = object_to_array(json_decode(input('address')));
    	// return $address;exit;
    	$goods = object_to_array(json_decode(input('goods')));
    	// return $goods;exit;
    	$con['title'] = $goods['0']['goods_name'];
    	
		$con['price'] = $this -> re -> jiSuanPrice($goods);
		// return $con['price'];
		if($con['price']==input('money')){
			$con['price'] = $con['price']*100;
		}else{
			return -2;exit;	
		}
		// $con['openid'] = input('openid');
		$con['code'] = input('code');
		$con['uid'] = input('uid');
		$con['cid']=input('cid');
		$con['shipping_fee']='0.00';//运费暂时不管
		$con['uniqid'] = uniqid();
		$con['sn'] =  date("YmdHis").uniqid();//暂时没有判断订单重复
		$con['out_trade_no'] = uniqid();
// return $con;exit;
		
		$a = $this -> re -> dingDan($address,$goods,$con);


		// return $a ;exit;
		if($a!=-1){
			$b = $this -> re -> order_pay($con);
			$res = $this -> weixin($con);
			return $res ;
		}else{
			$N = count($goods);
			if($N==1){
				return ['error'=>-1,'id'=>$goods['0']['id']];
			}else{
				$R = array();
				$R['goods'] = $goods;
				$R['buyPrice'] = ($con['price']/100).".00";
				$R['error'] = -2;
				return $R;
			}
		}

	}

	
	//服务与支付
    public function weixin($con){
    	// return $con;
    	$data = array(
			'body' => $con['title'],//input('post.body/s','','trim,strip_tags'),
			'attach' => $con['sn'],// input('post.attach/s','','trim,strip_tags'),
			'out_trade_no' => $con['out_trade_no'], //input('post.orderid/s','','trim,strip_tags'),
			'total_fee' => $con['price'],//input('post.total_fee/f',0,'trim,strip_tags')*100,//订单金额，单位为分，如果你的订单是100元那么此处应该为 100*100
			'time_start' => date("YmdHis"),//交易开始时间
			'time_expire' => date("YmdHis", time() + 300),//5分钟过期
			// 'goods_tag' => '虔诚膜拜',
			'notify_url' => config('URL').'api.php/goods/order/weixin_notify/cid/'.$con['cid'],
			'trade_type' => 'JSAPI',
			// 'openid' => $con['openid'],//自己传参用
			
			// 'product_id' => rand(1,999999),
		);
    	
    	
		$result = $this-> re ->weixin($data,$con);
		return $result;
			
    }

    //支付成功后的自己
    public function notifys(){
    	$sn = input('sn');
    	$a = $this -> re -> doNotify($sn);
    	return $a;
    }

//支付成功后的后台回调接口
	public function weixin_notify()
	{
		$cid = input('cid');
		$notify_data = file_get_contents("php://input");
		if(!$notify_data){
			$notify_data = $GLOBALS['HTTP_RAW_POST_DATA'] ?: '';
		}
		if(!$notify_data){
			exit('');
		}
		// $Pay = new Pay;
		$result = $this -> re->notify_weixin($notify_data,$cid);
		exit($result);
	}

	//xml->arr 并且 做paySign的签名相关
	public function xml_arr(){
		$xml = input('xml');
		$cid = input('cid');
		$out_trade_no = input('out_trade_no');
		$res = $this -> re -> xmlTOarr($xml,$cid,$out_trade_no);
		// $paySign = $this -> paySign($res['prepay_id']);

		return $res;
	}

	

    
 


}

// {"appid":"wx98463ed021a8fd32","attach":"58dcd96844d36","bank_type":"CMBC_CREDIT","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1430262502","nonce_str":"NOkPsKOfi2z7UwPM","openid":"odnlJvwxSIlKnv_00OYU_azmnRZ8","out_trade_no":"20170330180944","result_code":"SUCCESS","return_code":"SUCCESS","return_msg":"OK","sign":"17F1528D2114B3B662F64B2F2F7F4367","time_end":"20170330181001","total_fee":"1","trade_state":"SUCCESS","trade_type":"JSAPI","transaction_id":"4007322001201703305196919827"}

