<?php 
namespace api\goods\model;

use \think\Model;
use \think\Db;
use \think\Config;
use think\Validate;
use think\Log;
use think\Session;

class DoOrder{

	private function weixin_config($cid){
		changLiang($cid);
		vendor('wxpay.WxPay_Api');
		vendor('wxpay.WxPay_JsApiPay');
		vendor('wxpay.WxPay_Notify');
	}

//根据订单号查订单表(为兼容游客模式)
	public function sn_getListOrder($uid,$type,$page,$jige){
		$res = array();
		$i=0;
		foreach ($uid as $k => $v) {
			if($k<($page*$jige)  || $k>=(($page+1)*$jige)){
				continue;
			}else{
				if(!$type){
					$where=['order_sn'=>$v,'order_status'=>['<>',3]];
				}elseif($type== 1){//待支付
					$where=['order_sn'=>$v,'order_status'=>0,'shipping_status'=>0,'pay_status'=>0];
				}elseif($type==2){//待发货
					$where=['order_sn'=>$v,'order_status'=>0,'shipping_status'=>0,'pay_status'=>2];
				}elseif($type==3){//待收货
					$where=['order_sn'=>$v,'order_status'=>0,'shipping_status'=>2,'pay_status'=>2];
				}elseif($type==4){//交易成功
					$where=['order_sn'=>$v,'order_status'=>['<>',3],'shipping_status'=>1,'pay_status'=>2];
				}
				$res[$i] = db('order_info')->field(['order_id','order_sn','shipping_fee'=>'shipping_price','goods_amount'=>'total_amount','order_status','shipping_status','pay_status'])->where($where)->find();
				$goods = db('order_goods')->field(['goods_id','goods_name','goods_attr','goods_number','goods_price'=>'sum_price'])->where('order_id',$res[$i]['order_id'])->select();
				foreach ($goods as $key => $value) {
					$goods[$key]['thumb_url'] = db('goods')->where(['goods_id'=>$value['goods_id']])->value('goods_thumb');
				// return $goods[$key]['thumb_url'];exit;
					$goods[$key]['thumb_url'] = config('theURL').$goods[$key]['thumb_url'];
					if(!$value['goods_attr']){
						$goods[$key]['goods_attr'] = "暂无属性";
					}
				}
				if($res[$i]['order_status']==0 && $res[$i]['shipping_status']==0 && $res[$i]['pay_status']== 0){
					$res[$i]['order_status'] = '待支付';
				}elseif($res[$i]['pay_status']== 2 && $res[$i]['shipping_status'] = 0 && $res[$i]['order_status']== 0){
					$res[$i]['order_status'] = '待发货';
				}elseif($res[$i]['pay_status']== 2 && $res[$i]['shipping_status'] = 2 && $res[$i]['order_status']== 0){
					$res[$i]['order_status'] = '待收货';
				}elseif($res[$i]['shipping_status'] = 1 && $res[$i]['order_status']== 1){
					$res[$i]['order_status'] = '交易成功';
				}elseif($res[$i]['order_status']== 2){
					$res[$i]['order_status'] = '交易取消';
				}
				$res[$i]['goods']= $goods;
				$res[$i]['order']['orderStatus'] =$res[$i]['order_status'];
				$res[$i]['order']['orderSn'] =$res[$i]['order_sn'];
				$res[$i]['order']['subOrderSn'] =$res[$i]['order_sn'];
				$res[$i]['sub_order_sn'] = $res[$i]['order_sn'];
				if($res[$i]['order_status']== '待支付'){
					$res[$i]['order']['isButtonHidden'] =true;
				}else{
					$res[$i]['order']['isButtonHidden'] =false;
				}
				$i++;
			}
		}
		return $res;
	}


//查看订单列表
	public function doListOrder($uid,$type,$page){
		$jige = 10;
		// return json_decode($uid);exit;
		$uid = json_decode($uid);
		if(gettype($uid)=='integer'){
			$limit = $page*$jige.','.$jige;
			if(!$type){
				$where=['user_id'=>$uid,'order_status'=>['<>',3]];
			}elseif($type== 1){//待支付
				$where=['user_id'=>$uid,'order_status'=>0,'shipping_status'=>0,'pay_status'=>0];
			}elseif($type==2){//待发货
				$where=['user_id'=>$uid,'order_status'=>0,'shipping_status'=>0,'pay_status'=>2];
			}elseif($type==3){//待收货
				$where=['user_id'=>$uid,'order_status'=>0,'shipping_status'=>2,'pay_status'=>2];
			}elseif($type==4){//交易成功
				$where=['user_id'=>$uid,'order_status'=>['<>',3],'shipping_status'=>1,'pay_status'=>2];
			}
			$res = db('order_info')->field(['order_id','order_sn','add_time','shipping_fee'=>'shipping_price','goods_amount'=>'total_amount','order_status','shipping_status','pay_status'])->where($where)->order('order_id desc')->limit($limit)->select();
				// return $res;exit;
			foreach ($res as $k => $v) {
				$goods = db('order_goods')->field(['goods_id','goods_name','goods_attr','goods_number','goods_price'=>'sum_price'])->where('order_id',$v['order_id'])->select();
				foreach ($goods as $key => $value) {
					$goods[$key]['thumb_url'] = db('goods')->where(['goods_id'=>$value['goods_id']])->value('goods_thumb');
				// return $goods[$key]['thumb_url'];exit;
					$goods[$key]['thumb_url'] = config('theURL').$goods[$key]['thumb_url'];
					if(!$value['goods_attr']){
						$goods[$key]['goods_attr'] = "暂无属性";
					}
				}
				if($res[$k]['order_status']==0 && $res[$k]['shipping_status']==0 && $res[$k]['pay_status']== 0){
					$res[$k]['order_status'] = '待支付';
				}elseif($res[$k]['pay_status']== 2 && $res[$k]['shipping_status'] = 0 && $res[$k]['order_status']== 0){
					$res[$k]['order_status'] = '待发货';
				}elseif($res[$k]['pay_status']== 2 && $res[$k]['shipping_status'] = 2 && $res[$k]['order_status']== 0){
					$res[$k]['order_status'] = '待收货';
				}elseif($res[$k]['shipping_status'] = 1 && $res[$k]['order_status']== 1){
					$res[$k]['order_status'] = '交易成功';
				}elseif($res[$k]['order_status']== 2){
					$res[$k]['order_status'] = '交易取消';
				}
				$res[$k]['add_time'] = date('Y-m-d H:i:s',$res[$k]['add_time']);
				$res[$k]['goods']= $goods;
				$res[$k]['order']['orderStatus'] =$res[$k]['order_status'];
				$res[$k]['order']['orderSn'] =$res[$k]['order_sn'];
				$res[$k]['order']['subOrderSn'] =$res[$k]['order_sn'];
				$res[$k]['sub_order_sn'] = $res[$k]['order_sn'];
				if($res[$k]['order_status']== '待支付'){
					$res[$k]['order']['isButtonHidden'] =true;
				}else{

					$res[$k]['order']['isButtonHidden'] =false;
				}
				// $res[$k]['order']['isButtonShow'] = 
			}
		}else{
			if($uid['0']!=-1){
				$res = $this -> sn_getListOrder($uid,$type,$page,$jige);
			}else{
				return 0;
			}
			
		}
		return $res;

	}

	//订单里查看订单详情
	public function doShowOrder($sn){
		$order = db('order_info') -> where('order_sn',$sn) ->field(['order_id','order_sn','order_status','shipping_status','pay_status','consignee','province','city','district','address','mobile','add_time','shipping_fee','goods_amount','order_amount'])->find();
		$province = db('region')->where('region_id',$order['province'])->value('region_name');
		$city = db('region')->where('region_id',$order['city'])->value('region_name');
		$district = db('region')->where('region_id',$order['district'])->value('region_name');
		
		$res = array();
		$res['address']['consignee'] = $order['consignee'];
		$res['address']['mobile'] = $order['mobile'];
		$res['address']['detail_address'] = $province.$city.$district.$order['address'];

		$goods = db('order_goods')->field(['goods_id','goods_name','goods_attr','goods_price'=>'sum_price','goods_number'])->where('order_id',$order['order_id'])->select();
		// return $goods;exit;
		foreach ($goods as $key => $value) {
			if($value['goods_attr']==''){
				$goods[$key]['goods_attr'] = '该商品无属性';
			}
			$goods[$key]['thumb_url'] = db('goods')->where('goods_id',$value['goods_id'])->value('goods_thumb');
			$goods[$key]['thumb_url'] = config('theURL').$goods[$key]['thumb_url'];
		}
		$res['goods'] = $goods;

		$res['order']['order_amount'] = $order['goods_amount'];
		$res['order']['shipping_price'] = $order['shipping_fee'];
		$res['order']['total_amount'] = $order['order_amount'];
		$res['order']['order_sn'] = $order['order_sn'];
		$res['order']['created_at'] = date('Y-m-d H:i:s',$order['add_time']);

		if($order['order_status']==0 && $order['shipping_status']==0 && $order['pay_status']== 0){
			$res['order']['orderStatus'] = '待支付';
		}elseif($order['pay_status']== 2 && $order['shipping_status'] = 0 && $order['order_status']== 0){
			$res['order']['orderStatus'] = '待发货';
		}elseif($order['pay_status']== 2 && $order['shipping_status'] = 2 && $order['order_status']== 0){
			$res['order']['orderStatus'] = '待收货';
		}elseif($order['shipping_status'] = 1 && $order['order_status']== 1){
			$res['order']['orderStatus'] = '交易成功';
		}elseif($order['order_status']== 2){
			$res['order']['orderStatus'] = '交易取消';
		}

		return $res;

	}

	//取消订单
	public function doXiaoOrder($sn){
		$oid = db('order_info') -> where(['order_sn'=>$sn])->value('order_id');

		$a = db('order_info')->where('order_sn',$sn)->update(['order_status'=>2]);
		$res = db('order_goods')->field(['goods_id','goods_number'])->where(['order_id'=>$oid])->select();
		// return $oid;exit;
		
		foreach ($res as $key => $value) {
			$gn = db('goods') ->where('goods_id',$value['goods_id']) -> value('goods_number');
			$new_gn = $gn + $value['goods_number'];
			db('goods')-> where('goods_id',$value['goods_id']) -> update(['goods_number'=>$new_gn]);
		}

		return $a;
	}

	//数据库确认订单
	public function doOkOrder($sn){
		$a = db('order_info')->where('order_sn',$sn)->update(['order_status'=>1,'shipping_status'=>1]);
		return $a ;
	}

	//将订单失效
	public function doShiXiaoOrder($sn){
		$a = db('order_info')->where('order_sn',$sn)->update(['order_status'=>3]);
		return $a ;
	}

	//执行查看订单详情
	public function doLookOrder($data){
		$re =array();
		$ras = 0;
		foreach ($data['goods'] as $key => $value) {
			$re[$key] = db('goods')
				->field(['goods_id'=>'id','goods_name','goods_sn','market_price','shop_price'=>'real_price','goods_number','goods_thumb'=>'thumb_url'])
				->where('goods_id',$value['gid'])
				->find();
			$re[$key]['thumb_url'] = config('theURL').$re[$key]['thumb_url'];
			if($value['pid']){
				$goods_attr_id = db('products') -> where('product_id',$value['pid']) -> value('goods_attr');
				$goods_attr_id = explode('|',$goods_attr_id);
				$re[$key]['goods_attr'] = '';
				$ra = 0;
				foreach ($goods_attr_id as $k=> $v) {
					$re[$key]['goods_attr'] .= db('goods_attr') -> where('goods_attr_id',$v)->value('attr_value')." ";
					$ra += db('goods_attr') -> where('goods_attr_id',$v)->value('attr_price');
				}
				$ras += ($re[$key]['real_price'] + $ra)*$value['num'];
				$re[$key]['real_price'] = $re[$key]['real_price'] + $ra;
				$re[$key]['pid'] = $value['pid'];
			}else{
				$ras += $re[$key]['real_price']*$value['num'];
				$re[$key]['goods_attr'] = "该商品无属性";
				$re[$key]['pid'] = 0;
			}
			$re[$key]['good_number'] = $value['num'];
			$re[$key]['status'] = true;
			if($value['num']==1){
				$re[$key]['decr_class'] = 'disabled';
			}else{
				$re[$key]['decr_class'] = '';
			}
			if($value['num']==$re[$key]['goods_number']){
				$re[$key]['plus_class'] = 'disbaled';
			}else{
				$re[$key]['plus_class'] = '';
			}
		}
		$res['goods'] = $re;
		$res['buyPrice'] = $ras;
		return $res;
	}

	//下单前确认价格信息
	public function jiSuanPrice($goods){
		$price =0;
		foreach ($goods as $k => $v) {
			if($v['pid']){
				$p = db('goods') -> where('goods_id',$v['id'])->value('shop_price');

				$goods_attr_id = db('products') -> where('product_id',$v['pid'])->value('goods_attr');
				$goods_attr_id = explode('|',$goods_attr_id);
				foreach ($goods_attr_id as $ke => $val) {
					$jia = db('goods_attr') -> where('goods_attr_id',$val)->value('attr_price');
					$p += $jia;
				}
				$price +=$p*$v['good_number'];
			}else{
				$p = db('goods') -> where('goods_id',$v['id'])->value('shop_price');
				$price +=$p*$v['good_number'];
			}
		}
		return $price;
	}

	//下单
	public function dingDan($address,$goods,$con){
		//根据地址查地址id
		$address = $this -> addressToid($address);
		// return $con;exit;
		// return $address;
		//加订单表order_info
		$goods_amount = $con['price']/100;
		$order_amount = $con['price']/100+$con['shipping_fee'];
		if(!isset($address['mobile'])){
			$address['mobile'] = '13333333333';
		}
		$data = [
			'order_sn'=>$con['sn'],
			'user_id'=>$con['uid'],
			'consignee'=>$address['consignee'],
			'country'=>1,
			'province'=> $address['province_id'],
			'city'=>$address['city_id'],
			'district'=>$address['district_id'],
			'address'=>$address['address'],
			'tel'=>$address['mobile'],
			'mobile'=>$address['mobile'],
			'shipping_id'=>2,
			'shipping_name'=>'圆通速递',
			'pay_id'=>2,
			"pay_name"=>'银行汇款/转帐',
			'how_oos'=>'等待所有商品备齐后再发',
			'goods_amount'=>$goods_amount,
			'shipping_fee'=>$con['shipping_fee'],
			'order_amount'=>$order_amount,
			'referer'=>'本站',
			'add_time'=>time()
		];
		$rudan = db('order_info')->insert($data);
// return $rudan;exit;
		$order_id = db('order_info') -> where(['order_sn'=>$con['sn']]) ->value('order_id');
			
		//加订单商品表order_goods
		// return $goods;exit;
		foreach ($goods as $k => $v) {
			if($v['goods_attr'] == '该商品无属性'){
				$data_ = [
						'order_id'=>$order_id,
						'goods_id'=>$v['id'],
						'goods_name'=>$v['goods_name'],
						'goods_sn'=>$v['goods_sn'],
						'goods_number'=>$v['good_number'],
						'market_price'=>$v['market_price'],
						'goods_price'=>$v['real_price']
				];
			}else{
				$products = db('products')->field(['goods_attr','product_number'])->where(['product_id'=>$v['pid']])->find();
				// return $products;exit;
				$products['goods_attr'] = explode('|',$products['goods_attr']);
				$goods_attr_id = implode(',',$products['goods_attr']);
				$goods_attr = '';
				foreach ($products['goods_attr'] as $key => $value) {
					
					$goods_attr_ = Db::view('goods_attr',['attr_value','attr_price'])
						->view('attribute',['attr_name'],'goods_attr.attr_id=attribute.attr_id')
						->where('goods_attr_id',$value)
						->find();
						// return $goods_attr_ ;exit;
					if($goods_attr_['attr_price']){
						$goods_attr .= $goods_attr_['attr_name'].":".$goods_attr_['attr_value'].'['.$goods_attr_['attr_price'].'] ';
					}else{
						$goods_attr .= $goods_attr_['attr_name'].":".$goods_attr_['attr_value'].' ';
					}
				}
				if($v['good_number']>$products['product_number']){
					db('order_info')->where('order_id',$order_id) ->delete();
					db('order_goods') ->where('order_id',$order_id)->delete();
					return -1;exit;
				}
				$new_Pnumber = $products['product_number']-$v['good_number'];


				$data_ = [
						'order_id'=>$order_id,
						'goods_id'=>$v['id'],
						'goods_name'=>$v['goods_name'],
						'goods_sn'=>$v['goods_sn'],
						'goods_number'=>$v['good_number'],
						'market_price'=>$v['market_price'],
						'goods_price'=>$v['real_price'],
						'goods_attr'=> $goods_attr ,
						'goods_attr_id'=>$goods_attr_id
					];
				$p = db('products') -> where('product_id',$v['pid'])-> update(['product_number'=>$new_Pnumber]);
			}
			$dan = db('order_goods') -> insert($data_);
			// 
			$gnumber = db('goods') -> where('goods_id',$v['id']) -> value('goods_number');
			if($v['good_number']>$gnumber){
				db('order_info')->where('order_id',$order_id) ->delete();
				db('order_goods') ->where('order_id',$order_id)->delete();
				return -1;exit;
			}
			$new_Gnumber = $gnumber - $v['good_number'];

// return $new_Gnumber;exit;
			$g = db('goods') ->where('goods_id',$v['id'])->update(['goods_number'=>$new_Gnumber]);

			if($dan){
				continue;
			}else{
				$dan = 0 ;
				break;
			}
		}

		return $rudan;

	}

	//根据地址名称查id
	function addressToid($address){

	    $province_id = db('region') -> where('region_name',$address['provinceName']) -> value('region_id');
	    // return $province_id;exit;
	    if($province_id){
	    	$city = db('region')->field(['region_id','region_name']) ->where('parent_id',$province_id)->select();
	    	// return $city;exit;
	    	foreach ($city as $key => $value) {

	    		if($address['cityName'] == $value['region_name']){

	    			$city_id = $value['region_id'];
	    			$district = db('region') ->field(['region_id','region_name']) -> where('parent_id',$city_id)->order('region_id desc')->select();
	    			foreach ($district as $k => $v) {
	    				if($address['countyName'] == $v['region_name']){
	    					$district_id = $v['region_id']; break;
	    				}else{
	    					$district_id = $district['0']['region_id'];
	    				}
	    			}
	    			break;
	    		}
	    	}
	    	$address['province_id'] = $province_id;
	    	$address['city_id'] = $city_id;
	    	$address['district_id'] = $district_id;
	    	
	    }else{
	    	$address['province_id'] = 1;
	    	$address['city_id'] = 1;
	    	$address['district_id'] = 1;
	    	$address['address'] = $address['provinceName'].' '.$address['cityName'].' '.$address['countyName'].' '.$address['address'];
	    }
	    return $address;
	}

	//订单里面支付
	public function doPayOrder($sn){
		$list = db('order_info')->field(['order_amount','order_id'])->where('order_sn',$sn)->find();
		$goods=db('order_goods')->field(['rec_id'])->where('order_id',$list['order_id'])->select();
		$list['title'] = db('order_goods')->where('rec_id',$goods['0']['rec_id'])->value('goods_name');
		return $list;
	}

//将本站订单跟微信商户订单联系起来
	public function order_pay($con){
		db('order_pay')-> insert(['sn'=>$con['sn'],'out_trade_no'=>$con['out_trade_no'],'add_time'=>time()]);
	}

//做统一下单
	public function weixin($data,$con){
		
		$this->weixin_config($con['cid']);
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.WXPAY_APPID.'&secret='.WXPAY_APPSECRET.'&js_code='.$con['code'].'&grant_type=authorization_code';
		$o_s = object_to_array(getJson($url));


		// $tools = new \JsApiPay();
		// $openId = $tools->GetOpenid($data);
		$input = new \WxPayUnifiedOrder();
		// print_r($data['openid']);exit;
		
		$input->SetOpenid($o_s['openid']);
		$input->SetBody($data['body']);//传参设置
		$input->SetAttach($data['attach']);//传参设置
		$input->SetTotal_fee($data['total_fee']);//传参设置
		
		$input->SetOut_trade_no($data['out_trade_no']);
		$input->SetTime_start($data['time_start']);
		$input->SetTime_expire($data['time_expire']);
		// $input->SetGoods_tag($data['goods_tag']);
		$input->SetNotify_url($data['notify_url']);
		$input->SetTrade_type($data['trade_type']);
		// $input->SetProduct_id($data['product_id']);
		$order = \WxPayApi::unifiedOrder($input);
		// $openId['jsApiParameters'] = $tools->GetJsApiParameters($order);//跳转失去的信息加上js需要的jsApiParameters
		// $order['xml'] = str_replace('<![CDATA[','',$order['xml']);
		// $order['xml'] = str_replace(']]>','',$order['xml']);

		return $order;
		
	}

	//更改支付状态
	public function doNotify($sn){
		$a = db('order_info')-> where(['order_sn'=>$sn])->update(['pay_status'=>2,'pay_time'=>time()]);
		return $a;
	}

	//将小程序的xml格式转成数组
	public function xmlTOarr($xml,$cid,$out_trade_no){
		$this->weixin_config($cid);
		$inputs = new \WxPayUnifiedOrder();
		$arr = $inputs ->FromXml($xml);
		//做paySign
		$input = new \WxPayJsApiPay();
		$noncestr= \WxPayApi::getNonceStr();
		$input ->SetAppid(WXPAY_APPID);
		$input ->SetNonceStr($noncestr);
		$input ->SetPackage("prepay_id=".$arr['prepay_id']);
		$input ->SetSignType("MD5");
		$input ->SetTimeStamp(time());
		$input ->SetSign();
		
		$order['paySign'] = $input ->GetSign();
		$order['nonceStr'] = $noncestr;
		$order['package'] = $input ->GetPackage();
		$order['timeStamp'] = (string)$input ->GetTimeStamp();
		$prepay_id = substr($order['package'],10);
		db('order_pay') -> where('out_trade_no',$out_trade_no)->update(['prepay_id'=>$prepay_id]);

		return $order;


		// return $arr;
	}

	
// {"appid":"wxc3dd69ea07fd2199","attach":"201705252015105926cace966ff","bank_type":"CEB_CREDIT","cash_fee":"1","fee_type":"CNY","is_subscribe":"N","mch_id":"10058350","nonce_str":"Qj8udnnplurGFmoX","openid":"os9II0fogNVsAHZX8Xm7_GIYU6RM","out_trade_no":"201705252015105926cace966ff","result_code":"SUCCESS","return_code":"SUCCESS","return_msg":"OK","sign":"F83936F5BA26F883E377263902438B0F","time_end":"20170525201534","total_fee":"1","trade_state":"SUCCESS","trade_type":"JSAPI","transaction_id":"4007322001201705252561878603"}
	public function notify_weixin($data='',$cid)
	{

		if(!$data){
			return false;
		}
		$this->weixin_config($cid);
    	$doc = new \DOMDocument();
		$doc->loadXML($data);
		$out_trade_no = $doc->getElementsByTagName("out_trade_no")->item(0)->nodeValue;
		$transaction_id = $doc->getElementsByTagName("transaction_id")->item(0)->nodeValue;
		$openid = $doc->getElementsByTagName("openid")->item(0)->nodeValue;
		$input = new \WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = \WxPayApi::orderQuery($input);
		
		if(array_key_exists("return_code", $result) && array_key_exists("result_code", $result) && $result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS"){
			//改ecshop中的订单状态
			$this -> doNotify($result['attach']);
			//改order_pay表的支付状态
			db('order_pay')->where(['out_trade_no'=>$result['out_trade_no']])->update(['state'=>1,'pay_time'=>time()]);
			

			//购买成功发送模版
			$da = db('order_info')->field(['user_id'=>'uid']) -> where(['order_sn'=>$result['attach']]) ->find();
			
			$da['did'] = $cid;
			$da['form_id'] = db('order_pay')->where('out_trade_no',$result['out_trade_no'])->value('prepay_id');
			// $da['form_id'] = $result['transaction_id'];
			$da['sn'] = $result['attach'];

			$res = $this-> doPaySuccess($da);


			//保留做检测用
			$a = json_encode($result);
			db('ls') -> insert(['text'=>$a]);
			db('ls') -> insert(['text'=>json_encode($res)]);

			

			
			$notify = new \WxPayNotify();
			$notify->Handle(true);

			// 返回给微信确认
			echo $notify->ToXml();
						
		}
		return false;
	}

//发送购买成功（没有支付）的模版
	public function doPaySuccess($data){
		// changLiang($data['did']);
		$access_token = getAccessToken($data['did']);
		$dat['touser'] = db('users') -> where('user_id',$data['uid']) -> value('openid');
		$dat['template_id'] = 'GTKBkTFru8n6QGR-vWEsiYJf-wtdL-AhTIddMt0VslQ';
		$dat['page'] = '/pages/order-detail/order-detail?subOrderSn='.$data['sn'];
		$dat['form_id'] = $data['form_id'];

		$order = db('order_info') -> where('order_sn',$data['sn']) -> field(['order_id','add_time','order_amount'])->find();
		$goods= db('order_goods')->field(['goods_name'])->where('order_id',$order['order_id'])->select();

		if(count($goods)>1){
			$name = $goods[0]['goods_name']." 等";
		}else{
			$name = $goods[0]['goods_name'];
		}
		
		$dat['data']['keyword1']['value'] = date("Y-m-d H:i:s",$order['add_time']);
		$dat['data']['keyword1']['color'] = '#9b9b9b';
		
		$dat['data']['keyword2']['value'] = $name;
		$dat['data']['keyword2']['color'] = '#9b9b9b';

		$dat['data']['keyword3']['value'] = "￥ ".$order['order_amount'];
		$dat['data']['keyword3']['color'] = 'red';

		$dat['data']['keyword4']['value'] = $data['sn'];
		$dat['data']['keyword4']['color'] = '#9b9b9b';

		$dat['data']['keyword5']['value'] = '13109575376(微信同号)';
		$dat['data']['keyword5']['color'] = '#9b9b9b';

		$dat['emphasis_keyword'] = "keyword3.DATA" ;
		// return $dat;exit;
		$dat = json_encode($dat);
		

		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;

		$res = json_decode(httpPost($url,urldecode($dat)));
		return $res;


	}

	


}