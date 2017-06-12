<?php 
namespace api\index\controller;

use \think\Controller;
use \think\config;
use \think\Model;
use \think\Db;

use api\index\model\DoUser;

class User extends Controller{

	public $re;
 	public function _initialize(){ 
    	$re = new DoUser();
    	$this -> re = $re;
        
 	}


	//通过用户id，查看用户信息
	public function  userinf(){
		$uid = input('uid');
		$res = $this -> re -> doUinfo($uid); 
		if($res){
			return $res;

		}
	}


	public function collect(){
		$uid = input('uid');
		$page = input('page')?input('page'):1;
		$num = input('num')?input('num'):10;
		$list = $this -> re ->DoCollect($uid,$page,$num);

		if($list){
			return $list;
		}else{
			if($page==1){
				return -2;
			}else{
				return -1;
			}

		}

	}


	


}
?>