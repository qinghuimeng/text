<?php 
namespace app\index\model;
use think\Model;
use think\Db;
use think\Request;
class CateModel extends model{
	public function getCate($id){
		$arr = db('forum_forum')->field('name,fup,fid')->where('fup',$id)->select();
		foreach ($arr as $k => $v) {
			if($v['name'] == 'Discuz!'){
				unset($arr[$k]);
			}
		}
		array_multisort($arr);
		return $arr;
	}
	public function titleAdd($title,$fid,$uid,$message){
	    $oldpid = last_forum();
		// return $idArr;
	    $pid = $oldpid+ 1;
		$author = idtoval('common_member',$uid,'uid','username');
		$newpid = db('forum_post')->insertGetId(['authorid'=>$author,'subject'=>$title,'fid'=>$fid,'message'=>$message,'pid'=>$pid,'authorid'=>$uid]);
		return $pid;
	}
	//修改文章
	public function upActicle($pid,$newarr,$imgArr){
		$newstr = implode('|', $newarr);
		for ($i=0; $i < count($imgArr); $i++) { 
			if($i>8){
				unset($imgArr[$i]);
			}
		}
		$imgstr = implode(',',$imgArr);

		$t = db('forum_post')->where('pid',$pid)->update(['message'=>$newstr,'pic'=>$imgstr,'dateline'=>time()]);
		return $t;
	}

	
}


 


 ?>                                                                 