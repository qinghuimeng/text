<?php
namespace api\goods\controller;

use think\Controller;
use \think\Model;
use \think\Db;
use \think\Config;

use api\goods\model\DoGoods;


class Goods extends Controller
{
	public $re;
 	public function _initialize(){ 
    	$re = new DoGoods();
    	$this -> re = $re;
        
 	}

	public function index()
	{
		// 获取要显示的商品
		$cid = input('cid')?input('cid'):0;
		// changLiang($cid);
		// if(WXPAY_MCHID==10058350){
			$page = input('page')?input('page'):1;
			$num = input('num')?input('num'):5;
			$type = input('type')?input('type'):0;
			$not_cid = input('not_cid')?input('not_cid'):0;
			$goodslist = $this ->re -> dolist($cid,$type,$num,$page,$not_cid);
			if($goodslist){
				return $goodslist;
			}else{
				if($page==1){
					return -2;
				}else{
					return -1;
				}
			}
		// }else{
		// 	return -3;
		// }
	}

	

	// 商品规格
	public function showguige(){

		$id = input('goodsid');
		$uid = input('userid');
		$list = $this -> re -> goodsIdGetGoodsGuige($id,$uid);
		return $list;
	}


	// 商品详情
	public function  showgoods(){
		$id = input('goodsid');
		$uid = input('userid');
		$zfid = input('zfid');
		$list = $this -> re -> goodsIdGetGoodsShow($id,$uid,$zfid);
		return $list;

	}


	// 商品评论
	public function goodsPinglunList(){
		$id= input('id');
		$page = input('page')?input('page'):1;
		$num= input('num')?input('num'):5;
		$list = $this -> re -> DoGoodsPinglun($id,$page,$num);

		if($list==-1){
			if($page==1){
				// 没有数据
				return -2;
			}else{
				// 没有更多数据
				return -1;
			}
		}else{
			// 数据
			return $list;
		}
	}



	// 添加评论
	public function goodsPinglunAdd(){
		$arr['user_id'] = input('userid');
		$arr['content'] = input('content');
		$arr['id_value'] = input('goodsid');


		$add = $this -> re -> DoGoodsPinglunAdd($arr);
		if($arr){
			return $add;
		}

	}


	// 收藏商品和取消收藏
	public function collect(){
			$type = input('type');     //1是收藏，2是取消

			$uid = input('userid');
			$gid = input('goodsid');

			$edit = $this -> re -> DoCollect($type,$uid,$gid);

			if($edit){
				return $edit;
			}

	}



}
