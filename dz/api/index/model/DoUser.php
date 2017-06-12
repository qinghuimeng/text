<?php 
namespace api\index\model;

use \think\Model;
use \think\Db;
use api\goods\model\DoGoods;

class DoUser{

	//通过用户id，查看用户信息
	public function  userIdGetUserXinxi($id,$xinxi){
		$db = db('users');
		$arr = $db -> field([$xinxi])->where('user_id','=',$id) -> find();
		if($arr){
			return $arr;

		}
	}

	//查用户
	public function doUinfo($uid){

		$res = db('users')->field(['user_name'=>'nickName','touxiang'=>'avatarUrl'])->where(['user_id'=>$uid])->find();
		return $res;
	}

	// 查收藏
	public function DoCollect($uid,$page,$num){

		$db = db('collect_goods');
		$where = ['user_id'=>$uid];
		$list = $db ->field(['goods_id','add_time']) -> where($where) -> order('add_time desc')->limit(($page-1)*$num,$num)->select();

		$Goods = new DoGoods();

		foreach ($list as $key => $value) {
			# code...
			 $xinxi = ['goods_name'=>'name','goods_thumb'=>'img'];
			 $arr = $Goods -> GoodsIdGetGoodsXinxi($list[$key]['goods_id'],$xinxi);

			 $list[$key]['goods_name'] = $arr['name'];
			 $list[$key]['goods_img'] = Config('theURL').$arr['img'];
			 $list[$key]['time'] = date('Y-m-d',$list[$key]['add_time']);
		}
		if($list){
			return $list;
		}

	}


	


}
?>