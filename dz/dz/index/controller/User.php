<?php 
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;
use app\index\model\Douser;
header('content-type:text/html;charset="UTF-8"');
class User extends controller{

	public $re;
	public function _initialize(){
		$re = new Douser();
		$this->re = $re;
	}
	//将用户信息存储到数据库中
	public function getUser(){
		$code = input('code');
		$name = utfHan(urlencode(input('nickName')));
		$image = input('avatarUrl');
		$arr = $this->getOpneid($code);		
		$oldUid = idtoval('common_member',$arr['openid'],'openid','uid');
		
		
		if(isset($oldUid)){
			return $oldUid;
			exit();
		}
		$newArr = array('openid'=>$arr['openid'],'username'=>$name,'headimg'=>$image);
		// $newArr = array('openid'=>'123456','username'=>'23
		// 	56','headimg'=>'123456');
		$uid = $this->re->doInUser($newArr);
		return $uid;

		// return json($newArr);
	}

	
	//将当前的openid值获取到
	public function getOpneid($code){
		$appArr = Config('xiao');
		$appid = $appArr['appid'];
		$appscreat = $appArr['appscreat'];
		$url ='https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$appscreat.'&js_code='.$code.'&grant_type=authorization_code';
		$arr = curl($url);
		return $arr;
		
	}

	//我的帖子
	public function myTi(){
		// $page = 1;
		// $uid = 2;
		$page = input('page');
		$uid = input('uid');
		$tiarr = $this->re->selMytie($uid,$page);

		foreach ($tiarr as $k => $v) {
			$tiarr[$k]['dateline'] = date('Y-m-d',$v['dateline']);
			
			$newarr = array();
			$newarr = explode(',',$v['pic']);
			
			$tiarr[$k]['images'] = str_replace('\\','/',trim($newarr[0]));
		}
		
		$userArr = $this->re->chaUser($uid);
		// echo "<pre>";
		// print_r($tiarr);
		// echo "</pre>";
	
		return json(['page'=>$page,'msg'=>$tiarr,'user'=>$userArr]);
	}

	//我评论过的帖子
	public function myPing(){
		// $page = 1;
		// $uid = 8;
		$page = input('page');
		$uid = input('uid');
		$tiarr = $this->re->selMypl($uid,$page);

		// echo "<pre>";
		// print_r($tiarr);
		// echo "</pre>";
		return json($tiarr);
	}
	
	//我关注的人
	public function follow(){
		$uid = input('uid');
		// $uid = 2;
		$arr = $this->re->follow($uid);
		return json($arr);
	
	}
	//关注我的人
	public function concer(){
		$uid = $uid = input('uid');
		$arr = $this->re->concer($uid);
		return json($arr);
	}
	//收藏列表
	public function sc_list(){
		// $page = 1;
		// $uid = 8;
		$page = input('page');
		$uid = input('uid');
		$arr = $this->re->myLike($page,$uid);
		if($arr == -1){
			return -1;
		}else{
			return json($arr);
		}
		// echo "<pre>";
		// print_r($arr);
		// echo "</pre>";
		
	
	}


	//个人信息
	public function peopel(){
		// $uid = 12;
		$uid = input('uid');
		$arr = $this->re->doPeopel($uid);
		if($arr == -1){
			return -1;
		}else{
			return json($arr);
		}
		
		

	}
	//评论列表
		public function pl_list(){
		$id=input('uid');
		$arr = $this->re->pl_cont($id);
		if($arr == -1){
			return -1;
		}else{
			return json($arr);
		}
		// $id = 8;		
	}
	//将用户头像查出
	public function head(){
		$uid = input('uid');
		$head = idtoval('common_member',$uid,'uid','headimg');
		return $head;
	}
}

 ?>