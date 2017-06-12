<?php
namespace api\index\controller;

use think\Controller;
use \think\Model;
use \think\Db;
use \think\Config;

use api\index\model\DoIndex;
// use admin\index\controller\Login;

class Index extends Controller
{
	public $re;
 	public function _initialize(){ 
    	$re = new DoIndex();
    	$this -> re = $re;
        
 	}
	
	//第一版临时用，跟新后可删
	public function index()
	{
		$num = input('page')?input('page'):1;

		$appId = '5fab92242716357852'; //请填入你有赞店铺后台-营销-有赞API的AppId
		$appSecret = '98917417a2befd1f255d3b4f82eadca4';//请填入你有赞店铺后台-营销-有赞API的AppSecret
		$client = new \YZSignClient($appId, $appSecret);

		$method = 'kdt.items.onsale.get';//要调用的api名称
		$methodVersion = '1.0.0';//要调用的api版本号

		$params = [
			'page_size'=>'2',
			'page_no'=>$num,
			'fields'=>'item_imgs,title',

		];

		$res = $client->post($method, $methodVersion, $params);
		if(is_array($res['response']['items']) && !empty($res['response']['items'])){
			foreach ($res['response']['items'] as $key => $value) {
				$res['response']['items'][$key]['touxiang'] = config('img').'ls.jpg';
				$res['response']['items'][$key]['name'] = '省园林局赵启光同志';
				$res['response']['items'][$key]['shi'] = '晋中';
				$res['response']['items'][$key]['xian'] = '榆社县';

			}
			return $res['response']['items'];
		}else{
			return -1;
		}
		
	}

	//index页面进去点赞页面，调取地区分类
	public function diqu(){
		$res = $this -> re -> doDiQu();
		return $res;
	}

	//获取地区简介
	public function diqujianjie(){
		$uid = input('uid');
		$did = input('did');
		$res = $this -> re -> doDiQuJianJie($uid,$did);
		return $res;
	}


	

}
