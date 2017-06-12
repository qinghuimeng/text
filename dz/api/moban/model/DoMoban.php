<?php 
namespace api\moban\model;

use \think\Model;
use \think\Db;
use \think\Config;

class DoMoban{

	//发送购买成功（没有支付）的模版
	public function doPaySuccess($data){
		// changLiang($data['did']);
		$dat['access_token'] = getAccessToken($data['did']);
		$dat['touser'] = db('users') -> where('user_id',$data['uid']) -> value('openid');
		// $dat['template_id'] = 'GTKBkTFru8n6QGR-vWEsiYJf-wtdL-AhTIddMt0VslQ';
		// $dat['page'] = '/pages/index/index';
		// $dat['form_id'] = substr($data['form_id'],10);
		
		// $dat['data']['keyword1']['value'] = '今天';
		// $dat['data']['keyword1']['color'] = '#f00';
		
		// $dat['data']['keyword2']['value'] = 'test';
		// $dat['data']['keyword2']['color'] = '#ff0';

		// $dat['data']['keyword3']['value'] = '天价';
		// $dat['data']['keyword3']['color'] = '#00f';

		// $dat['data']['keyword4']['value'] = $data['sn'];
		// $dat['data']['keyword4']['color'] = '#159';

		// $dat['data']['keyword5']['value'] = '0351-1234567';
		// $dat['data']['keyword5']['color'] = '#951';

		// $dat['emphasis_keyword'] = "keyword3.DATA" ;
		// // return $dat;exit;
		// $dat = json_encode($dat);
		

		// $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$access_token;

		// $res = json_decode(httpPost($url,urldecode($dat)));
		// return $res;
		// 
		// 
		return $dat;


	}

	


	


}
?>