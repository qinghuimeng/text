<?php 
namespace app\index\model;
use think\Model;
use think\Db;
use think\Request;
use think\Config;
class Douser extends model{
	public function doInUser($arr){
		$uid = db('common_member')->insertGetId($arr);
		return $uid;
	}
	//查出我的帖子
	public function selMytie($uid,$page){
		$nowpage = ($page - 1)*10;
		$m = db('forum_post')->where(['authorid'=>$uid,'message'=>''])->delete();

		$arr = db('forum_post')->where(['authorid'=>$uid,'first'=>1])->limit($nowpage,10)->select();
		foreach ($arr as $k => $v) {
			$tu = db('forum_attachment')->where('pid',$v['pid'])->field('aid,tableid')->find();
			$tuArr = db('forum_attachment_'.$tu['tableid'])->where('aid',$tu['aid'])->field('attachment')->find();
			
			$arr[$k]['pic'] =Config('url').$tuArr['attachment'];

		}
		return $arr;
	}
	//将我评论过的帖子
	public function selMypl($uid,$page){
		$nowpage = ($page - 1)*10;
		
		$arr = db('home_comment')->alias('a')
			->join('jsx_forum_post w','w.pid=a.id')
			->join('jsx_common_member m','m.uid=w.authorid')
			->field('m.headimg,m.username,m.uid,w.pid,w.subject,w.message,w.dateline,a.message')
			->where('a.uid',$uid)
			->limit($page,10)
			->select();
			
		if(count($arr) == 0){
			return -1;
			exit();
		}else{
			$newarr = $this->tieChu($arr);
			return $newarr;
		}
			
		

	}
	//我关注的人
	public function follow($uid){
		$arr = Db::table('jsx_common_member')
				->alias('user')
				->join('jsx_home_follow dept','dept.followuid= user.uid')
				->where('dept.uid',$uid)				
				->select();
	
			return $arr;
	}
	//关注我的人
	public function concer($uid){
		$arr = Db::table('jsx_common_member')
				->alias('user')
				->join('jsx_home_follow dept','dept.uid= user.uid')
				->where('dept.followuid',$uid)				
				->select();
		return $arr;
	}
	//查用户信息
	public function chaUser($uid){
		$userarr = db('common_member')->field('username,headimg')->where('uid',$uid)->find();
		$userarr['name'] = $userarr['username'];
		$fencont = db('home_follow')->where('uid',$uid)->count();
		$guancont = db('home_follow')->where('followuid',$uid)->count();
		$userarr['fencont'] = $fencont;
		$userarr['guancont'] = $guancont;
		return $userarr;
	}
	//我的收藏
	public function myLike($page,$uid){
		$nowpage = ($page - 1)*10;
		$arr = db('home_favorite')->alias('a')
			->join('jsx_forum_post b','a.id=b.pid')
			->join('jsx_common_member w','b.authorid = w.uid')
			->where('a.uid',$uid)
			->limit($nowpage,10)
			->field('b.pid,b.message,b.subject,b.dateline,w.uid,w.username,w.headimg,b.ratetimes,b.click')
			->select();
		
		if(count($arr) == 0){
			return -1;
			exit();
		}else{
			$newarr = $this->chuLike($arr);
			return $newarr;
		}
			
			

	}
	//评论
	public function pl_cont($id){
		$arr=db('home_comment')->alias('a')->join('jsx_forum_post b','a.id=b.pid')->join('jsx_common_member w','w.uid = b.authorid')->where('a.uid',$id)->field('a.message content,b.click,b.subject,b.ratetimes,a.dateline,b.message,b.pid,w.username')->select();
		if(count($arr) == 0){
			return -1;
		}else{
			$arr =$this->chuLike($arr);
			return $arr;
		}
	}

	//个人中心
	public function doPeopel($uid){
		$arr = db('common_member')->where('uid',$uid)->field('headimg,username,uid')->find();
		if(empty($arr['headimg'])){
			$arr['headimg'] = Config('tourl').'/bbs/uc_server/images/noavatar_small.gif';
		}
		$plcont = db('home_comment')->where('uid',$uid)->count();
		$likecont = db('home_favorite')->where('uid',$uid)->count();
		$arr['plcont'] = $plcont;
		$arr['liekcont'] = $likecont;
		return $arr;
	}


	//帖子列表处理函数
	public function tieChu($arr){
		foreach ($arr as $k => $v) {
				$tabarr = db('forum_attachment')->where('pid',$v['pid'])->order('pid')->field('aid,tableid')->select();
				if(count($tabarr) != 0){
					$arr[$k]['attach'] = $tabarr;
				}	
				if(empty($v['headimg'])){
					$arr[$k]['headimg'] =Config('tourl').'/bbs/uc_server/images/noavatar_small.gif';
				}			
			}
			
		
			//将图片查出来
			foreach ($arr as $k => $v) {
				$newarr = array();
				if(isset($v['attach'])){

					for ($i=0; $i < count($v['attach']); $i++) { 
						$newarr[0] =$v['attach'][$i]['tableid'];
						$newarr[] =$v['attach'][$i]['aid'];
					}
						// $newarr[$a['tableid']][] = $a['aid'];
						$arr[$k]['attach'] = $newarr;
					}
					
				}
				

			//判断有没有网络图片
			foreach ($arr as $k => $v) {
				//修改网络图片的环境
				if(preg_match('/img/i', $v['message'])){
					$newstr = str_replace('[img]', 'mt', $v['message']);
					$newstr = str_replace('[/img]', 'mt', $newstr);
					$newstr = str_replace('[img=640,854]', 'mt', $newstr);
					$tuArr = explode('mt', trim($newstr));
					for ($i=0; $i < count($tuArr); $i++) { 
						
						if(empty($tuArr[$i])){
							unset($tuArr[$i]);
						}
					}
					$arr[$k]['tu'] = $tuArr;
				}else if(preg_match('/attach/i', $v['message'])){
				
					$newstc = str_replace("[attach]", 'mt', $v['message']);
						$newstr = trim(str_replace("[/attach]", 'mt', $newstc));
						$xianarr = explode('mt', $newstr);
						$intArr = array();
						for ($j=0; $j < count($xianarr); $j++) { 
							str_replace(' ', ' ', $xianarr[$j]);
							if(empty($xianarr[$j])){
								unset($xianarr[$j]);
							}
							
						}
						
						$arr[$k]['tu'] = $xianarr;
					}else{
						$xianarr = explode('。',$v['message']);
						$arr[$k]['tu'] = $xianarr;
					}
				}
				

			
			//将图片的地址查出来
			foreach ($arr as $k => $v) {
				if(isset($v['attach'])){
					$e = reset($v['attach']);
					for ($i=1; $i < count($v['attach']); $i++) { 
						$arr[$k]['pic'][]=Config('url').idtoval('forum_attachment_'.$e,$v['attach'][$i],'aid','attachment')	;
					}
					
				}
			}
			//提取导读
			foreach ($arr as $k => $v) {
				for ($i=0; $i < count($v['tu']); $i++) { 
					if(is_numeric($v['tu'][$i])){
						unset($v['tu'][$i]);
					}
					sort($v['tu']);
				for ($j=0; $j < count($v['tu']); $j++) { 
						$arr[$k]['dao'] = $v['tu'][$j];
					}
				

				}
			}
			//图片数组
			foreach ($arr as $k => $v) {
			
				for ($i=0; $i < count($v['tu']); $i++) { 
					if(preg_match('/http/i', $v['tu'][$i])){
						
						$arr[$k]['pic'][]= $v['tu'][$i];

					}
				}
				if(isset($arr[$k]['pic'])){
					sort($arr[$k]['pic']);
				}
				// ksort($arr[$k]['pic']);
			}

			foreach ($arr as $k => $v) {
				if(!isset($v['pic'])){
					$arr[$k]['pic'][] = Config('tourl').'/bbs/data/attachment/forum/201705/18/175003llj15j581xhw0zp1.png';
				}
			}
	}

	// 处理我的收藏
	public function chuLike($arr){
			foreach ($arr as $k => $v) {
				$tabarr = db('forum_attachment')->where('pid',$v['pid'])->order('pid')->field('aid,tableid')->select();
				if(count($tabarr) != 0){
					$arr[$k]['attach'] = $tabarr;
				}	
				if(empty($v['headimg'])){
					$arr[$k]['headimg'] =Config('tourl').'/bbs/uc_server/images/noavatar_small.jpg';
				}				
			}
			

			//将图片查出来
			foreach ($arr as $k => $v) {
				$newarr = array();
				if(isset($v['attach'])){

					for ($i=0; $i < count($v['attach']); $i++) { 
						$newarr[0] =$v['attach'][$i]['tableid'];
						$newarr[] =$v['attach'][$i]['aid'];
					}
						// $newarr[$a['tableid']][] = $a['aid'];
						$arr[$k]['attach'] = $newarr;
					}
					
				}

			//判断有没有网络图片
			foreach ($arr as $k => $v) {
				//修改网络图片的环境
				if(preg_match('/img/i', $v['message'])){
					$newstr = str_replace('[img]', 'mt', $v['message']);
					$newstr = str_replace('[/img]', 'mt', $newstr);
					$newstr = str_replace('[img=640,854]', 'mt', $newstr);
					$tuArr = explode('mt', trim($newstr));
					for ($i=0; $i < count($tuArr); $i++) { 
						
						if(empty($tuArr[$i])){
							unset($tuArr[$i]);
						}
					}
					$arr[$k]['tu'] = $tuArr;
				}else if(preg_match('/attach/i', $v['message'])){
				
					$newstc = str_replace("[attach]", 'mt', $v['message']);
						$newstr = trim(str_replace("[/attach]", 'mt', $newstc));
						$xianarr = explode('mt', $newstr);
						$intArr = array();
						for ($j=0; $j < count($xianarr); $j++) { 
							str_replace(' ', ' ', $xianarr[$j]);
							if(empty($xianarr[$j])){
								unset($xianarr[$j]);
							}
							
						}
						
						$arr[$k]['tu'] = $xianarr;
					}else{
						$xianarr = explode('。',$v['message']);
						$arr[$k]['tu'] = $xianarr;
					}
				}
				

		
			//将图片的地址查出来
			foreach ($arr as $k => $v) {
				if(isset($v['attach'])){
					$e = reset($v['attach']);
					
						$arr[$k]['pic']=Config('url').idtoval('forum_attachment_'.$e,$v['attach']['1'],'aid','attachment')	;
					}
					
				
			}
					return $arr;
	
	}
}
 ?>
