<?php 
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Config;
use app\index\model\CateModel;
class Cate extends controller{
	public $re;
	public function _initialize(){
		$re = new CateModel();
		$this->re = $re;
	}
	
	public function catelist(){
		$id = input('fup');
		if(!isset($id)){
			$id = 0;
		}
		$arr = $this->re->getCate($id);
	
		return json($arr);
	}
	//提交标题和分类
	public function addTit(){
		// $title= "aaa";
		// $fid = '33';
		// $uid = 2;
		$message = ' ';
		
		$title = input('title');
		$fid = input('fid');
		$uid = input('uid');
		$pid = $this->re->titleAdd($title,$fid,$uid,$message);

		// echo "<pre>";
		// print_r($pid);
		// echo "</pre>";
		// echo $pid;
		if(isset($pid)){
			return json(['code'=>1,'pid'=>$pid]);
		}else{
			return json(['code'=>0]);
		}
	}

	//文章上传接口
	public function actiUp(){
		$arr = input();
		$pid = $arr['pid'];

		// var_dump($arr);
		$listArr = $arr['x']['list'];
		$newarr = array();
		$imgArr = array();
		for ($i=0; $i < count($listArr); $i++) { 

			if($listArr[$i]['type'] == 'IMAGE'){
				trim($listArr[$i]['description']);
				 array_push($imgArr, $listArr[$i]['description']);

				$newarr[$i] = '<img>'.$listArr[$i]['description'];
				
			}else{
				trim($listArr[$i]['description']);
				$newarr[$i] = '<p>'.$listArr[$i]['content'];
			}
			
		}

		$t = $this->re->upActicle($pid,$newarr,$imgArr);
		
		if($t){
			return json(['code'=>1]);
		}else{
			return json(['code'=>0]);
		}
		
		// return json($listArr);
	}
	//上传文件
	public function upload(){
		
		$file = request()->file('file');
		 $info = $file->move(ROOT_PATH . 'DZUpload' . DS . 'uploads');
	    if($info){
	       
	        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	        $data =  "https://api.sx988.cn/DZUpload/uploads/".$info->getSaveName();
	      
	    }else{
	        // 上传失败获取错误信息
	        $data = $file->getError();
	    }
	    
	    return trim($data);
	    // return json($data);





	
	}


}

 ?>