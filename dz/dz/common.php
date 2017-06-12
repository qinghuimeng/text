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

// 应用公共文件
function curl($url){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	//执行
	$outopt=curl_exec($ch);
	$outoptarr=json_decode($outopt,TRUE);
	//关闭
	curl_close($ch);
	return $outoptarr;
}

//查出一条数据
function idtoval($db,$val,$key,$r){
	$arr = db($db)->where([$key=>$val])->field($r)->find();
	// return $arr;
	return $arr[$r];
}

//名字emoji图片屏蔽
function utfHan($name){
    $t = json_encode($name);
    $z = urldecode($t);
    $regex = '/(\\\u[ed][0-9a-f]{3})/i';  
    $z = json_encode($z);  
    $text = preg_replace($regex,'□', $z); 
    $wename = json_decode($text);
    $newname = str_replace('"', "", $wename);
    
    return $newname;
}

//下载图片函数
function curlImg($url,$filename){
   	$ch = curl_init ();    
    curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );    
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );    
    curl_setopt ( $ch, CURLOPT_URL, $url );    
    ob_start ();    
    curl_exec ( $ch );    
    $return_content = ob_get_contents ();    
    ob_end_clean ();    
        
    $return_code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );  
  
    $fp= @fopen($filename,"a"); //将文件绑定到流      
    fwrite($fp,$return_content); //写入文件     
    return $filename;
}
//打印数组
function p($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}
//将帖子的最新一条数据查出来
function last_forum(){
    $arr = db('forum_post')->field('pid')->order('pid desc')->find();
    return $arr['pid'];
}
//将帖子的最后一个位子查出来
function last_position($tid){
    $arr = db('forum_post')->field('position')->order('position desc')->where('tid',$tid)->find();
    return $arr['position'];
}
//将回复的内容替换
function str_rep($pid,$ptid,$username,$time,$oldmsg,$newmsg){
    $str = '[quote][size=2][url=forum.php?mod=redirect&goto=findpost&pid=pid&ptid=ptid][color=#999999]username 发表于 oldtime[/color][/url][/size]
oldmsg[/quote]newmsg';
    $newstr = strtr($str, 'pid', $pid);
    $newstr = strtr($str, 'ptid', $ptid);
    $newstr = strtr($str, 'username', $username);
    $newstr = strtr($str, 'oldtime', $time);
    $newstr = strtr($str, 'oldmsg', $oldmsg);
    $newstr = strtr($str, 'newmsg', $newmsg);
    return $newstr;
}
//回复评论 将帖子的id和评论人的
 function replays($str){
     $newarr = explode('quote', $str);
     $xianrr = explode('&',$newarr['1']);
     $num = strpos($xianrr['2'], '=');
     $pid = substr($xianrr['2'], $num+1);
     $newxian = explode(']',$xianrr['3']);
     $newwen = explode('[',$newxian['2']);
     
     $newtext = trim($newxian['5']);
     $newnum = strpos($newtext, "[");
     $newText = substr($newtext, 0,$newnum);
     
     $huiwen = $newwen['0'];
     $arr =  array(array(
         're_zhan'=>$huiwen,'re_message'=>$newText,'re_pid'=>$pid),'message'=>trim(substr($newarr['2'], 1)));
     return $arr;
 }



