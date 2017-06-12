<?php
namespace api\categories\controller;

use think\Controller;
use \think\Model;
use \think\Db;
use \think\Config;

use api\categories\model\DoCategories;


class Categories extends Controller
{
	public $re;
 	public function _initialize(){ 
    	$re = new DoCategories();
    	$this -> re = $re;
        
 	}
	
	public function index()
	{
		// 获取县名称
		$list = $this -> re -> get_categories_tree(0,2);
		if($list){
			return $list;
		}
	}


	

}
