<?php
namespace api\index\controller;

use think\Controller;
use \think\Model;
use \think\Db;
use \think\Config;

use api\index\model\CheckLogin;

class Login extends Controller
{
	public $re;
 	public function _initialize(){ 
    	$re = new CheckLogin();
    	$this -> re = $re;
        
 	}
	
 	
 	
	//第一次登陆注册
	public function index(){
		$cid= input('cid');
		changLiang($cid);
		vendor('Wxcx_encryptedData.wxBizDataCrypt');
		
		//获取用户的openid  +  sessionKey
		$code = input('code');
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.WXPAY_APPID.'&secret='.WXPAY_APPSECRET.'&js_code='.$code.'&grant_type=authorization_code';
		$o_s = object_to_array(getJson($url));
		// return $o_s;
		$sessionKey = $o_s['session_key'];
		$encryptedData=input('encryptedData');

		$iv = input('iv');

		$pc = new \WXBizDataCrypt(WXPAY_APPID, $sessionKey);
		$errCode = $pc->decryptData($encryptedData, $iv, $data );

		// return  object_to_array(json_decode($data,true));exit;
		if ($errCode == 0) {
		    $data = object_to_array(json_decode($data,true));
		    
		    $data['nickName']=removeEmoji($data['nickName']);
			if(!$data['nickName']){
				$data['nickName'] = 'user-'.uniqid();
			}
		    $res = $this -> re -> adduser($data);
		    
		    return json($res) ;

		} else {
		    return -100 ;
		}

	}

	//获取小程序名称
	public function cid(){
		$cid = input('cid');
		changliang($cid);
		$re = ['cat_name'=>CAT_NAME,'banner'=>CAT_BANNER,'weitouxiang'=>CAT_WEITOUXIANG];
		return $re;
	}



	//添加访问次数
	public function num(){
		$uid = input('uid');
		$res = $this-> re -> doNum($uid);
		return $res;
	}

	//离开时记录最后一次的时间+ip
	public function last(){
		$uid= input('uid');
		$ip = $_SERVER["REMOTE_ADDR"];
		$this ->re->doLast($uid,$ip);
	}

	

}
