<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use \think\Db;
use \think\Config;
// 应用公共文件
// 
// 对象成数组
function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }
 
    return $obj;
}

//数组成对象
function array2object($array) {
  if (is_array($array)) {
    $obj = new StdClass();
    foreach ($array as $key => $val){
      $obj->$key = $val;
    }
  }
  else { $obj = $array; }
  return $obj;
}

// 过滤掉emoji表情
function removeEmoji($str){
    

    $tmpStr = json_encode($str);
    $tmpStr2 = @preg_replace("#(\\\u[e|d][0-9a-f]{3})#ie","",$tmpStr);
    $return = json_decode($tmpStr2);
        
    return $return;
}

//定义使用参数为常量
function changLiang($cid){

    if($cid){
        $WX_info = db('diqu')->field(['cat_name','banner','weitouxiang','appid','appsecret','mchid','key_'])->where('cat_id',$cid)->find();
        // return $WX_info;exit;
        define('WXPAY_APPID', $WX_info['appid']);//微信公众号APPID
        define('WXPAY_APPSECRET', $WX_info['appsecret']);//微信公众号appsecret
        define('WXPAY_MCHID', $WX_info['mchid']);//微信商户号MCHID
        define('WXPAY_KEY', $WX_info['key_']);//微信商户自定义32位KEY
        define('CAT_NAME',$WX_info['cat_name']);//小程序名称
        $banner = $WX_info['banner']?$WX_info['banner']:'images/topbg.jpg';
        $weitouxiang = $WX_info['weitouxiang']?$WX_info['weitouxiang']:'images/topphone.jpg';
        define('CAT_BANNER',config('theURL').$banner);
        define('CAT_WEITOUXIANG',config('theURL').$weitouxiang);
    }else{
        define('WXPAY_APPID', 'wx9953f82bf3706bcb');//微信公众号APPID
        define('WXPAY_APPSECRET', '49b4743057f7f0a21d6453df3fe6ccb9');//微信公众号appsecret
        define('WXPAY_MCHID', '10058350');//微信商户号MCHID
        define('WXPAY_KEY', 'LLBweixin818LLBweixin818LLBweixi');//微信商户自定义32位KEY
        define('CAT_NAME','山西');//小程序名称
        define('CAT_BANNER',config('theURL').'images/topbg.jpg');
        define('CAT_WEITOUXIANG',config('theURL').'images/topphone.jpg');
    }
}

//curl微信请求
function getJson($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

//获取access_token
function getAccessToken($did) {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = db('diqu') -> where('cat_id',$did) ->field(['access_token','time'])->find();
    if ($data['time'] < time()) {
      // 如果是企业号用以下URL获取access_token
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".WXPAY_APPID."&secret=".WXPAY_APPSECRET;
      $res = json_decode(httpGet($url));
      $access_token = $res->access_token;
      if ($access_token) {
        $data['time'] = time() + 7000;
        $data['access_token'] = $access_token;
        db('diqu') -> where('cat_id',$did) -> update(['access_token'=>$data['access_token'],'time'=>$data['time']]);
      }
    } else {
      $access_token = $data['access_token'];
    }
    return $access_token;
  }

//get请求数据
function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }

//post请求数据
function httpPost($url,$data=''){
    $curl = curl_init();
    //设置CURLOPT_RETURNTRANSFER将curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    // curl_setopt($curl, CURLOPT_HEADER, 0); //设置header
    //启用时会发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
    curl_setopt($curl, CURLOPT_POST, TRUE); 
    if($data){
        //全部数据使用HTTP协议中的"POST"操作来发送。
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    } 

    //需要获取的URL地址
    curl_setopt($curl, CURLOPT_URL, $url);

    // 执行一个cURL会话
    $res = curl_exec($curl);
    if(curl_errno($curl)){
        return curl_errno($curl);
    }
    curl_close($curl);
    return $res;
  }
