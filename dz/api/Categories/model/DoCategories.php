<?php 
namespace api\categories\model;

use \think\Model;
use \think\Db;


class DoCategories{


		// 获取县名称
		// cat_id 上级id
		// type   一级还是二级  默认是1
		public function get_categories_tree($cat_id=0,$type=1){

			$db = db('diqu');
			$list = $db -> field(['cat_id' => 'id','cat_name' => 'cate_name','sort_order']) -> where('parent_id','=',$cat_id) -> order('sort_order asc') -> select();
			if($list){
				if($type == 1){
					// 如果是1级直接返回数据
					return $list;
				}else{
					foreach ($list as $key => $value) {
						$list2 = $db -> field(['cat_id' => 'id','cat_name' => 'cate_name']) -> where('parent_id','=',$list[$key]['id']) -> order('sort_order asc') ->select();
						$list[$key]['er'] = $list2; 
						$list[$key]['img'] = config('theURl').'images/fenlei/shi_'.$list[$key]['id'].'.jpg'; 
					}
					return $list;
				}


			}else{
				return 0;
			}
		}

		// 分类Id查看分类名称
		public function categoryIdGetCategoryName($id){
			$db = db('diqu');
			$list = $db -> field(['cat_name' => 'name']) -> where('cat_id','=',$id)->find();
			return $list['name'];
		}

		// 分类id查看父id
		public function categoryIdGetFuCategoryId($id){
			$db = db('diqu');
			$list = $db -> field(['parent_id' => 'id']) -> where('cat_id','=',$id)->find();
			return $list['id'];
		}

		// 查看是不是市
		public function categoryIsShi($id){
			$db = db('diqu');
			$list = $db -> field(['parent_id' => 'id']) -> where('cat_id','=',$id)->find();
			return $list['id'];
		}



		// 判断这个分类有没有子分类，有则返回
		public function categoryFuIdGetZiCategoryId($id){
			$db = db('diqu');
			$list = $db -> field(['cat_id' => 'id']) -> where('parent_id','=',$id)->select();
			$html = "";
			if($list){
				foreach ($list as $key => $value) {
					if($key==0){
						$html .= $list[$key]['id'];
					}else{
						$html .= ','.$list[$key]['id'];
					}

				}
			}
			return $html;
		}
	


}
?>