<?php 
namespace api\goods\model;

use \think\Model;
use \think\Db;

use api\Categories\model\DoCategories;
class DoTuijian{



	public function TuijianIdGetTuijianXinxi($id){

		$db = db('tuijianren');
		$arr = $db -> field(['name','img','id','beizhu'])-> where(['id'=>$id])->find();
		return $arr; 
	}



}
?>