<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;
use app\index\model\IndexModel;


header('content-type:text/html;charset="UTF-8"');
class Show extends controller{

  //   public function show(){

	 //    $page = input('page');

	 //  	 $arr = db('forum_post')->alias('a')->join('jsx_common_member w','a.authorid = w.uid')->join('jsx_forum_forum z','z.fid=a.fid')->field('a.pid,a.author,a.message,a.pic,a.click,a.ratetimes,a.dateline,a.subject,w.openid,w.headimg,w.username,w.uid,z.fid,z.name')->limit($page-1,5)->order('a.dateline desc')->select();

	 //  	 if(count($arr) == 0){
	 //  	 	return -1;
	 //  	 	exit();
	 //  	 }
		//     foreach ($arr as $k => $v) {
		//     	$newarr = explode(',',$v['pic']);
		//     	for ($i=0; $i < count($newarr); $i++) { 
		//     		$newarr[$i] = trim(str_replace('\\','/',$newarr[$i]));
		//     		if($i>8){
		//     			unset($newarr[$i]);
		//     		}
		    		
		//     	}

		//     	$arr[$k]['newpic'] = $newarr;
		//     }
		//    return json($arr);
  //  	 }
  //    public function xiangqing(){
  //    	// $pid = 33;
  //    	// $uid = 8;
		// $pid =input('pid');
		// $uid = input('uid');
		// $arr = db('forum_post')->alias('a')->join('jsx_common_member w','a.authorid = w.uid')->field('a.message,a.click,a.ratetimes,a.dateline,a.subject,w.headimg,w.username,a.authorid')->where("a.pid",$pid)->find();
		// $zanarr = db('click')->where(['articid'=>$pid,'userid'=>$uid])->find();
	
		// if(count($zanarr)){
		// 	// echo 0;
		// 	$zanstate = 0;
		// }else{
		// 	// echo 1;
		// 	$zanstate = 1;
		// }
	
		
		// $guancont = db('home_follow')->where(['uid'=>$uid,'followuid'=>$arr['authorid']])->count();

		// 	if($guancont == 0){
		// 		$arr['guan'] = 1;
		// 	}else{
		// 		$arr['guan'] = 0;
		// 	}
		// 	$newarr = array();
		// 	$mseeArr = explode('|',$arr['message']);
		// 	for($i = 0;$i<count($mseeArr);$i++){
		// 		if(strpos($mseeArr[$i],'https')){
		// 			$newarr[$i]['type'] = 'IMAGE';
		// 			$mseeArr[$i] = str_replace('\\','/',$mseeArr[$i]);
		// 			$newarr[$i]['content'] = trim(substr($mseeArr[$i],5));
		// 		}else{
		// 			$newarr[$i]['type'] = 'TEXT';
		// 			$newarr[$i]['content'] = trim(substr($mseeArr[$i],3));
		// 		}

		// 	}

		// 	$oldarr = db('home_comment')->alias('a')->join('jsx_common_member w','a.uid = w.uid')->field('a.pid,a.dateline,a.uid,w.username,w.headimg,a.message,a.cid')->where('a.id',$pid)->select();
		// 	// echo $oldarr;
			
		// 	$arr['hao']=$oldarr;
		// 	$arr['wen'] = $newarr;	
		// 	$arr['zanstate'] = $zanstate;
		// 	$arr['addtime'] = date('Y-m-d',$arr['dateline']);
		// 	 return json($arr);	
  //   }
  //添加评论
    public function pinglun(){
	    $plid=input('plid');
	    $tid = input('pid');
	    $uid = input('uid');
	    $dateline = $_SERVER['REQUEST_TIME'];
	    $pid = last_forum()+1;
	    $tid = idtoval('forum_post',$pid,'pid','tid');
// 	           如果是第一次评论
	    if($plid == "''"){		        
	    	$plid = 0;
	    	$message = input('cont');
	    }else{
	        $newMessage = input('cont');
	    	// echo 0;
	       $plArr = db('forum_post')->alias('a')
	              ->join('jsx_common_member u','u.uid=a.authorid')
	              ->field('a.message,a.dateline,a.pid,a.tid,u.username')
	              ->where('pid',$plid);
	       $huitime = date('Y-m-d H:i',$plArr['dateline']);
	       $message = str_rep($plArr['pid'],$plArr['tid'],$plArr['username'],$huitime,$plArr['message'],$newMessage);
	    }
	    $position = last_position($tid)+1;
	    $first = 0;
	    $fid = idtoval('forum_post',$tid,'tid', 'fid');
	    $name = idtoval('common_member',$uid,'uid', 'username');
        
	    $data = [
	       'pid'=>$pid,
	       'fid'=>$fid,
	       'tid'=>$tid,
	       'author'=>name,
	       'authorid'=>$uid,
	       'dateline'=>$dateline,
	       'message'=>$message,
	       'first'=>$first,
	        'position'=>$position
	    ];
	    	$list=db('home_comment')->insertGetId($data);
	    	$m = db('forum_post')->where('pid',$pid)->setInc('ratetimes',1);
	    	
			if($list){
				 //return input('cont');
				return json(['code'=>1,'cid'=>$list]);	
			}else{
				// 数据
				return json(['code'=>-1]);
			}
			
    	
    	}
    public function click(){
    	$pid = input('pid');
    	$uid = input('uid');
    
		$zanarr = db('click')->where(['articid'=>$pid,'userid'=>$uid])->find();
		if(count($zanarr) == 0){
			$data=[
				'userid'=>$uid,
				'articid'=>$pid,
				'addtime'=>time()
				];
			$add=db('click')->insert($data);
			$aa=db('forum_post')->where('pid',input('pid'))->setInc('click',1);
		}else{
			$add=db('click')->where(['id'=>$zanarr['id']])->delete();
			$aa=db('forum_post')->where('pid',input('pid'))->setDec('click',1);
		}
		
		if($add&&$aa){
			return json(['code'=>1]);
			
		}else{
			// 数据
			return json(['code'=>-1]);
		 }
    }


	//关注事件
	public function guan(){
		$uid = input('uid');
		$followuid = input('followuid');
		$data = [
			'uid' => $uid,
			'followuid'=>$followuid,
			'username' =>idtoval('common_member',$uid,'uid','username'),
			'fusername'=>idtoval('common_member',$followuid,'uid','username'),
			'dateline' =>time()
		];
		
		$t = db('home_follow')->insert($data);
		return $t;


	}

	//现在帖子

	//我的收藏
	public function myLike(){
		$uid = input('uid');
		$pid = input('pid');
		
		$data = [
			'uid'=>$uid,
			'id'=>$pid,
			'idtype'=>'tid',
			'title'=>idtoval('forum_post',$pid,'pid','subject'),
			'description'=>' ',
			'dateline'=>time(),

		];
		
		$m = db('home_favorite')->insert($data);
		if($m){
			return 1;
		}else{
			return -1;
		}
	}

	//查询数据
	public function show(){
		// $page = 1;  
		$page = input('page');
		$nowpage = ($page-1)*5;
		 $page = input('page');
		$arr = db('forum_post')->alias('a')
			->join('jsx_common_member w','a.authorid = w.uid')
			->join('jsx_forum_forum f','f.fid = a.fid')
			->join('jsx_forum_thread t','t.tid=a.tid')
			->field('a.pid,a.message,a.subject,a.dateline,w.uid,w.username,w.headimg,f.fid,f.name,a.ratetimes,a.click,t.views')
			->limit($nowpage,5)
			->where('first',1)
			->select();

		if(count($arr) == 0){
			return -1;
			exit();
		}else{
			$newarr = $this->chuTie($arr);
			return json($newarr);
		}
			
			
			// var_dump($arr);
			// echo "<pre>";
			// print_r($newarr);
			// echo "</pre>";
	}


	//详情页
	public function xiangqing(){
// 		$pid =input('pid');
// 		$uid = input('uid');
		$pid = 7;
		$uid = 8;
		$arr = db('forum_post')->alias('a')
			->join('jsx_common_member w','a.authorid = w.uid')
			->join('jsx_forum_forum f','f.fid = a.fid')
			->join('jsx_forum_thread t','t.tid=a.tid')
			->field('a.pid,a.message,a.subject,a.dateline,w.uid,w.username,w.headimg,f.fid,f.name,a.click,a.ratetimes,a.authorid,t.views')
			->where(['a.pid'=>$pid,'first'=>1])
			->find();
		$tid = idtoval('forum_post',$pid,'pid','tid');
		$m = db('forum_thread')->where('tid',$tid)->setInc('views',1);

		// 	//将点赞的列表查出来
		$clickArr = db('click')->alias('a')
					->join('jsx_common_member b','b.uid=a.userid')
					->where('a.articid',$pid)
					->where('a.userid','neq',$uid)
					->field('b.headimg')
					->limit(110)
					->select();
		$clickNum = db('click')->field('userid')->where(['articid'=>$pid,'userid'=>$uid])->count();
		$arr['clickstate'] = $clickNum;
		
		
		foreach ($clickArr as $k => $v) {
			if(empty($v['headimg'])){
				$clickArr[$k]['headimg'] = Config('tourl').'/bbs/uc_server/images/noavatar_small.jpg';
			}
		}

	

		$arr['likeimg'] = $clickArr;
		// $arr['liekcont'] = $likecont;
		$zanarr = db('click')->where(['articid'=>$pid,'userid'=>$uid])->find();
		$likearr = db('home_favorite')->where(['uid'=>$uid,'id'=>$pid])->find();

		if(empty($arr['headimg'])){
			$arr['headimg'] = Config('tourl').'/bbs/uc_server/images/noavatar_small.jpg';
		}
		if(count($likearr)){
			$likestate = 0;
		}else{
			$likestate = 1;
		}
	
		if(count($zanarr)){
			// echo 0;
			$zanstate = 0;
		}else{
			// echo 1;
			$zanstate = 1;
		}		
		$guancont = db('home_follow')->where(['uid'=>$uid,'followuid'=>$arr['authorid']])->count();
		if($guancont == 0){
			$arr['guan'] = 1;
		}else{
			$arr['guan'] = 0;
		}
		$attArr = db('forum_attachment')->where('pid',$pid)->field('aid,tableid')->select();
		
		if(count($attArr) == 0){
			$tuArr = array();
		}else{
			foreach ($attArr as $k => $v) {
				$newarr[0] = $v['tableid'];
				$newarr[] = $v['aid'];
			}
			//将图片查出来
			for ($i=1; $i < count($newarr); $i++) { 
				$tuArrp[$newarr[$i]] = Config('url').idtoval('forum_attachment_'.$newarr['0'],$newarr[$i],'aid','attachment');
				$tuArr[] = $newarr[$i];
			}
		}
	
		//将文章中的图片地址替换
		$newmsg = str_replace("[attach]", 'mt', $arr['message']);
		
		$newmsg = str_replace('[align=left]',' ',$newmsg);
		$newmsg = str_replace('[align=right]',' ',$newmsg);
		$newmsg = str_replace('[/align]',' ',$newmsg);
		$newmsg = trim(str_replace("[/attach]", 'mt', $newmsg));
		
		$msArr = explode('mt', $newmsg);
		//删除空数组
		for ($i=0; $i < count($msArr); $i++) { 
			if(empty($msArr[$i])){
				unset($msArr[$i]);
			}
		}

		//将数组中的原来图片显示
		ksort($msArr);
		if(!isset($msArr['0'])){
			sort($msArr);
			for ($i=0; $i < count($msArr); $i++) { 
				if(empty($msArr[$i])){
					unset($msArr[$i]);
				}
			}
			rsort($msArr);
			sort($msArr);
		}
		
		for ($i=0; $i < count($msArr); $i++) { 
			if(in_array($msArr[$i],$tuArr)){
				$id = array_search($msArr[$i], $msArr);
				$msArr[$id] = $tuArrp[$msArr[$i]];
			}
		}
		
		//返回是不是图片和文字
		$newmsgArr = array();
		for ($i=0; $i < count($msArr); $i++) { 
			if(preg_match('/http/i',$msArr[$i])){
				$newmsgArr[$i]['type'] = 'IMAGE';
				$newmsgArr[$i]['content'] = $msArr[$i];
			}else{
				$newmsgArr[$i]['type'] = 'TEXT';
				$newmsgArr[$i]['content'] = $msArr[$i];
			}
		}
		$oldarr = db('forum_post')
		          ->where('tid',$pid)
		          ->where('first','eq',0)
		          ->field('dateline,author,authorid,message')
		          ->limit(0,5)
		          ->order('dateline desc')
		          ->select();
        //将评论的信息匹配
        if(count($oldarr)!=0){
            foreach ($oldarr as $k=>$v){
                if(strpos($v['message'], 'quote')){
                    $oldarr[$k]['hui'] = 1;
                    $haoarr = replays($v['message']);
                    $oldarr[$k]['message'] = $haoarr['message'];
                    $oldarr[$k]['huiarr'] = $haoarr['0'];
                    
                    
                }else{
                    $oldarr[$k]['hui'] = 0;
                }
            }
        }
      
		
		
		$arr['wen'] = $newmsgArr;
		$arr['hao']=$oldarr;
		$arr['likestate'] = $likestate;
		$arr['zanstate'] = $zanstate;
		$arr['addtime'] = date('Y-m-d',$arr['dateline']);
// 		p($arr);
		return json($arr);
		// var_dump($msArr);
		// echo $newmsg;
		
		// echo "<pre>";
		// print_r($arr);
		// echo "</pre>";
	}

	public function page(){
		$page = input('page');
		$pid = input('pid');
		$oldarr = db('forum_post')
		->where('tid',$pid)
		->where('first','neq',0)
		->field('dateline,author,authorid,message,')
		->limit(0,5)
		->order('dateline desc')
		->select();
		
		if(count($oldarr)!=0){
		    if(strpos($v['message'], 'quote')){
		        $oldarr[$k]['hui'] = 1;
		        $haoarr = replays($v['message']);
		        $oldarr[$k]['message'] = $haoarr['message'];
		        $oldarr[$k]['huiarr'] = $haoarr['0'];    
		    }else{
		        $oldarr[$k]['hui'] = 0;
		    }
			return json(['code'=>1,'msg'=>$oldarr]);
		}else{
			return json(['code'=>0]);
		}
	}




	public function get_categories_tree($cat_id=0,$type=1){

			$db = db('forum_forum');
			$list = $db -> field('fid,name ,img,displayorder') -> where(['fup'=>$cat_id,'status'=>1]) -> order('displayorder asc') -> select();
			if($list){
				if($type == 1){
					// 如果是1级直接返回数据
					return $list;
				}else{
					foreach ($list as $key => $value) {
						$list2 = $db -> field('fid,name,img,displayorder') -> where('fup','=',$list[$key]['fid']) -> order('displayorder asc') ->select();
						$list[$key]['er'] = $list2; 
					    $list[$key]['img'] = config('theURl').'images/'.$list[$key]['fid'].'.jpg'; 
					}
					return json($list);
				}


			}else{
				return 0;
			}
		}
    public function cate(){
		 $list=$this->get_categories_tree(0,0);
		 if($list){
			return $list;
		}
		 
	 }

	
	public function cate_list(){
			$page = input('page');
			$nowpage = ($page-1)*10;
			$fid=input('fid');
			$page=input('page');
			$page = input('page');
			$arr = db('forum_post')->alias('a')
				->join('jsx_common_member w','a.authorid = w.uid')
				->join('jsx_forum_forum f','f.fid = a.fid')
				->field('a.pid,a.message,a.subject,a.dateline,w.uid,w.username,w.headimg,f.fid,f.name,a.ratetimes,a.click')
				->where('a.fid',$fid)
				->limit($nowpage,5)
				->select();
			if(count($arr) == 0){
				return -1;
				exit();
			}else{
				$newarr = $this->chuTie($arr);
				// echo "<pre>";
				// print_r($newarr);
				// echo "</pre>";
				return json($newarr);
			}
			
	}

	//帖子处理函数
	public function chuTie($arr){

		foreach ($arr as $k => $v) {
				$tabarr = db('forum_attachment')->where('pid',$v['pid'])->order('pid')->field('aid,tableid')->select();
				if(count($tabarr) != 0){
					$arr[$k]['attach'] = $tabarr;
				}
				if(empty($v['headimg'])){
					$arr[$k]['headimg'] = Config('tourl').'/bbs/uc_server/images/noavatar_small.jpg';
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
					$newstc = str_replace('[align=left]',' ',$newstc);
					$newstc = str_replace('[align=right]',' ',$newstc);
					$newstc = str_replace('[/align]',' ',$newstc);
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
			// return $arr;
			// exit();
			foreach ($arr as $k => $v) {
				if(isset($v['tu'][0])){
					
					for ($i=0; $i < count($v['tu']); $i++) { 
						if(is_numeric($v['tu'][$i])){
							unset($v['tu'][$i]);
						}
						sort($v['tu']);
						if(count($v['tu'])!=0){
							for ($j=0; $j < count($v['tu']); $j++) { 
								$arr[$k]['dao'] = $v['tu'][$j];
							}
						}										
					}
				}else{
					$arr[$k]['dao'] = '';
					// $arr[$k]['dao']=0;
				}
				
			}
			//图片数组
			foreach ($arr as $k => $v) {
			
				for ($i=0; $i < count($v['tu']); $i++) { 
					if(isset($v['tu'][0])){
						if(preg_match('/http/i', $v['tu'][$i])){						
							$arr[$k]['pic'][]= $v['tu'][$i];
						}
					}
					
				}
				if(isset($arr[$k]['pic'])){
					sort($arr[$k]['pic']);
				}
				// ksort($arr[$k]['pic']);
			}

			return $arr;
	}

	//banner图
	public function banner(){
		$arr = db('forum_thread')->alias('a')
			->join('jsx_forum_post w','w.tid=a.tid')
			->join('jsx_forum_attachment e','e.tid=a.tid')
			->field('e.aid,e.tableid,w.pid')
			->where('a.digest',1)
			->order('a.dateline desc')
			->limit(10)
			->select();

			foreach ($arr as $k => $v) {
				$newarr[] = './bbs/data/attachment/forum/'.idtoval('forum_attachment_'.$v['tableid'],$v['aid'],'aid','attachment');
			}


			// $newarr = array('./bbs/data/attachment/forum/201705/24/151626j8o378tj99ojxrla.png','./bbs/data/attachment/forum/201705/24/151702kppdpkkdldblrpxw.png','./bbs/data/attachment/forum/201705/24/151725dnnnzvcpzlgv5l5k.png');
			
		
			for ($j=0; $j < count($newarr); $j++) { 
					
					$path= $this->chuTu($newarr[$j],$j);
					if(!empty($path)){
						$newImgarr[] = $path;
					}	
			}


			//取出三条
			for ($i=0; $i < 3; $i++) { 
				$newpath = str_replace('./','/',$newImgarr[$i]);
				$banarr[] = Config('tourl').$newpath;
			}
			return json($banarr);
			// echo "<pre>";
			// print_r($banarr);
			// echo "</pre>";
	}

	//处理图片
	public function chuTu($path,$i){
		$image = \think\Image::open($path);
		$wid = $image->width();
		$hig = $image->height();
		if($wid>=715 & $hig>=290){
			$newpath = './DzBanner/images/crop'.$i.'.jpg';
			$image->thumb(715, 290,\think\Image::THUMB_FIXED)->save($newpath);

			return $newpath;

		}
	}



	//热议接口
	public function Datate(){
	    $fid = input('fid');
	    // $fid = 8;
	    if($fid == 0){
	        $arr = $this->allData($fid);
	    }else{
	        $arr = $this->clData($fid);
	    }
	 
	    if(count($arr)==0){
	        $arr = $this->allData(0);
	        return json(['code'=>1,'msg'=>$arr]);
	    }else{
	        
	        // p($arr);
	        return json(['code'=>1,'msg'=>$arr,'img'=>'https://api.sx988.cn/ecshop/images/201705/1496203360378457030.jpg']);
	    }
	}
	//所有分类的帖子
	public function allData($fid){

	    $oneArr = db('forum_post')
	    ->alias('a')
	    ->join('jsx_common_member t','t.uid=a.authorid')
	    ->field('a.pid,a.dateline,a.author,t.headimg,a.subject')
	    ->order('a.dateline desc')
	    ->where('first',1)
	    ->limit(4)
	    ->select();
	    foreach ($oneArr as $k => $v) {
	        if(empty($v['headimg'])){
	            $oneArr[$k]['headimg'] = Config('tourl').'/bbs/uc_server/images/noavatar_small.jpg';
	        }
	    }
	    return $oneArr;
	}

	//不是山西的
	public function clData($fid){
	    //查出fup是不是最新的
	    $fup = idtoval('forum_forum',$fid,'fid','fup');
	    
	    if($fup == 0){
	        $fuparr = db('forum_forum')->field('fid')->where('fup',$fid)->select();
	        $fidarr = array();
	        foreach ($fuparr as $k => $v) {
	            $fidarr[] = $v['fid'];
	        }
	        $where = ['a.fid'=>['in',$fidarr]];
	    }else{
	        $where = ['a.fid'=>$fid];
	    }
	    
	    
	    $otherArr = db('forum_post')
	    ->alias('a')
	    ->join('jsx_common_member t','t.uid=a.authorid')
	    ->field('a.pid,a.dateline,a.author,t.headimg,a.subject')
	    ->order('a.datelime desc')
	    ->where($where)
	    ->where('first',1)
	    ->order('a.dateline desc')
	    ->limit(4)
	    ->select();
	    foreach ($otherArr as $k => $v) {
	        if(empty($v['headimg'])){
	            $otherArr[$k]['headimg'] = Config('tourl').'/bbs/uc_server/images/noavatar_small.jpg';
	        }
	    }
	    return $otherArr;
	}
	
	
	
	
	

}



