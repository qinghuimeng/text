<?php
namespace api\moban\controller;

use think\Controller;
use \think\Model;
use \think\Db;
use \think\Config;

use api\moban\model\DoMoban;
// use admin\index\controller\Login;

class Moban extends Controller
{
	public $re;
 	public function _initialize(){ 
    	$re = new DoMoban();
    	$this -> re = $re;
        
 	}
	
	//购买成功（没有支付）返回的模版
	public function paysuccess(){
		$da['uid'] = input('uid');
			
		$da['did'] = input('did');
		// $da['form_id'] = db('order_pay')->where('out_trade_no',$result['out_trade_no'])->value('prepay_id');
		// $da['form_id'] = $result['transaction_id'];
		// $da['sn'] = $result['attach'];

		$res = $this->re-> doPaySuccess($da);
		return $res;
	}


	

}
