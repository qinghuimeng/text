<?php 
namespace api\index\model;

use \think\Model;
use \think\Db;


class CheckLogin{
	//注册时用户
	public function adduser($data){
		$uid = db('users') -> where(['openid'=>$data['openId']])->value('user_id');
		$duid = db('common_member') -> where(['openid'=>$data['openId']])->value('uid');
		// return $duid;exit;
		$data['nickName']=removeEmoji($data['nickName']);
		if(!$data['nickName']){
			$data['nickName'] = 'user-'.uniqid();
		}
		if(!$uid){
			$dat = ['user_name'=>$data['nickName'],'sex'=>$data['gender'],'openid'=>$data['openId'],'touxiang'=>$data['avatarUrl'],'reg_time'=>time(),'visit_count'=>'1'];
			$uid = db('users') -> insertGetId($dat);
		}
		if(!$duid){
			$newArr =['openid'=>$data['openId'],'username'=>$data['nickName'],'headimg'=>$data['avatarUrl']];
			$duid = db('common_member')->insertGetId($newArr);

		}
		
		// return $duid;exit;
		// $res = array('uid'=>$uid,'duid'=>$duid);
		$res = $uid.','.$duid;
		return $res;

	}

	//每次访问添加的信息
	public function doNum($uid){
		$visit_count = db('users') -> where(['user_id'=>$uid]) ->value('visit_count');
		$new_visit_count = $visit_count+1;
		$a = db('users') -> where('user_id',$uid)->update(['visit_count'=>$new_visit_count]);
		return $a;
	}

	//离开时添加时间and ip
	public function doLast($uid,$ip){
		
		db('users') -> where('user_id',$uid)->update(['last_login'=>time(),'last_ip'=>$ip]);
	}
	


}

//https://api.sx988.cn/api.php/index/login/index/iv/ydKRA3Kr5CsfGiyz4klxIA==/sessionKey/iucV8sZqRgo2hHqgU4BiOg==/encryptedData/+HSEVVU7U9KDz/ZvpbPBvcWXl63BQdSktq1nKvc6GlqrDxnP6GwWl/X52ZMlT4sbaCnNSo7M5yItX8y8kZFA9nsL6horHD8jue+9yIZCLNKM6xvo3aaV92ryQXsCb3q/NnY9YqhSs0HE0tqTW7B973Qkjou513s96fURFTO8/KJ2IpBxp8VSvjGrvLxCxYIB+QUgxGa+OLjL8uoK1V9Gb+IVMd0Jdj4Wv5amXwZG5YoG4ctxxA5gg5s2urctIesyrDQEIFvhc/IooTn4T8K8v+BNT1OpIcTX8H6D+QxX7TZb511anUTaWMfzGJGpmqfNyxxtlAqYisKVuf5TCATtcBAJhP6MgOXU0aDr8eyn/f+F2MWdrgc3E0kj4+/dv0Pm7VPN1naizIItyhfZL2vgtc9ldYtllhywMu9zgPNXFTiE0IpuX40o4aoj2BZS1aA02XKhcLUhQADE1qnL4N0Iq+iIXzJ77yGhSUOhbsd2h68= ?>