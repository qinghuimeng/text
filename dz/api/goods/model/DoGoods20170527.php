<?php 
namespace api\goods\model;

use \think\Model;
use \think\Db;
use \think\Config;
use api\Categories\model\DoCategories;
use api\goods\model\DoTuijian;
use api\index\model\DoUser;

class DoGoods{

	// 列表页展示
	// cid  分类id
	// type 推荐 
	// num  显示几个
	// page 页数

	public function dolist($cid=0,$type=0,$num,$page,$not_cid){

		$db = db('goods');
		$where['is_delete']=['=',0];
		$where['goods_id'] = ['>',0];
		if($type==1){
			$where['is_best'] = ['=',1];
		}
		if($cid){
			// 查看他是不是父分类
			$category = new DoCategories;
			$isShi= $category -> categoryIsShi($cid);
			// 如果是0，则是市，去查他下面的县
			// 不是0，直接查县的商品
			if($isShi == 0){
				$cids = $category -> categoryFuIdGetZiCategoryId($cid);
			}else{
				$cids = $cid;
			}
			
			if($not_cid){
				$where['diqu'] = ['not in',$cids];
				// $list =  $db ->field(['goods_id'=>'id','goods_name'=>'title','tuijianren'=>'tid','cat_id'=>'cid','goods_brief'=>'jianjie']) -> where($where) -> whereNotIn('cat_id',$cids) -> limit($limit,$num) -> order('goods_id desc') -> select();
			}else{
				$where['diqu'] = ['in',$cids];
				// $list =  $db ->field(['goods_id'=>'id','goods_name'=>'title','tuijianren'=>'tid','cat_id'=>'cid','goods_brief'=>'jianjie']) -> where($where) -> limit($limit,$num) -> order('goods_id desc') -> select();
			}
		}
		$limit =$num*($page-1);
			$list =  $db ->field(['goods_id'=>'id','goods_name'=>'title','tuijianren'=>'tid','diqu'=>'cid','goods_brief'=>'jianjie']) -> where($where) -> limit($limit,$num) -> order('sort_order desc,goods_id desc')  -> select();
			// return $list;exit;
		

		

		
		// echo $db ->getlastsql();
		if($list){
			$category = new DoCategories;
			$tuijian = new DoTuijian;
			foreach ($list as $key => $value){

				$isShii = $category -> categoryIsShi($list[$key]['cid']);
				// 判断是不是直接属于市
				if($isShii==0){
					$list[$key]['shi'] = $category -> categoryIdGetCategoryName($list[$key]['cid']);		//市名称
					$list[$key]['shi_id'] = $list[$key]['cid'];												//市ID
				}else{

					$list[$key]['xian'] = $category -> categoryIdGetCategoryName($list[$key]['cid']);		//县名称
					$list[$key]['xian_id'] = $list[$key]['cid'];
					$list[$key]['shi_id'] = $category -> categoryIdGetFuCategoryId($list[$key]['cid']); 	//市id
					$list[$key]['shi'] = $category -> categoryIdGetCategoryName($list[$key]['shi_id']); 	//市名称
				}


				$arr = $tuijian -> TuijianIdGetTuijianXinxi($list[$key]['tid']);			
				$list[$key]['touxiang'] ='https://api.sx988.cn/ecshop/'.$arr['img'] ;					//推荐人头像
				$list[$key]['name'] =$arr['name'] ;														//推荐人姓名


				$list[$key]['item_imgs'] = $this -> goodsIdGetGoodsImg($list[$key]['id']);
			}
			return $list;
		}else{
			return 0;
		}

	}

	// 根据商品id去查他的商品图片
	public function goodsIdGetGoodsImg($id){

		$db = db('goods_gallery');
		$arr = $db -> where('goods_id','=',$id) -> order('img_order asc')->select();

		// 重新组装数组
		$arrs = array();
		foreach ($arr as $key => $value) {
			$arrs[] = Config('theURL').$arr[$key]['img_url'];
		}

		return $arrs;
	}

		public function goodsIdGetGoodsGuige($id,$uid){
		$db = db('goods');
		$arr = $db -> field(['headline','goods_name'=>'name','market_price'=>'m_price','shop_price'=>'price','goods_id'=>'id','goods_thumb'=>'img','goods_number']) -> where('goods_id','=',$id) -> find();
		$arr['img'] = Config('theURL').$arr['img'];	// 商品主图
		// 去查看规格
		$list = $this -> getgoodsproperties($id);
		$arr['shuxing'] = $list['shuxing']?$list['shuxing']:-1;
		$arr['guige'] = $list['guige']?$list['guige']:-1;

		if($list['guige']){
			$arr['isguige'] = 1;
		}else{
			$arr['isguige'] = 0;
		}
		$arr['kucun'] = $list['kucun']?$list['kucun']:-1;


		if($arr){
			return $arr;
		}

	}


	// 根据ID去查商品详情页信息
	// id  商品id
	// uid 用户id
	public function goodsIdGetGoodsShow($id,$uid,$zfid){
		$db = db('goods');
		$arr = $db -> field(['headline','goods_name'=>'name','market_price'=>'m_price','shop_price'=>'price','goods_id'=>'id','goods_desc'=>'desc','goods_thumb'=>'img','goods_number','tuijianren','uid_num']) -> where('goods_id','=',$id) -> find();
		$arr['headline_num'] = mb_strlen($arr['headline']);
		//获取访问该商品的用户信息
		$arr['touxiang'] = Db::view('goods_visit',['id'=>'g_v_id','visit_uid'=>'uid'])
						->view('users',['touxiang'=>'url'],'goods_visit.visit_uid=users.user_id')
						->where(['goods_id'=>['=',$id],'visit_uid'=>['<>',$uid]])
						->order('id desc')
						->select();
		
		$is_in = db('goods_visit') -> where(['goods_id'=>$id,'visit_uid'=>$uid]) ->value('id');
		$u['uid'] = $uid;
		$u['url'] = db('users') ->where('user_id',$uid)->value('touxiang');
		if($arr['touxiang']){
			if(count($arr['touxiang'])>=94){
				if($is_in){
					array_unshift($arr['touxiang'],$u);
					// return $arr['touxiang'];
				}else{
					if($u['url'] && $uid && $uid!=1 && is_numeric($uid)){
						$new_num = db('goods')->where('goods_id',$id)->value('uid_num');
						$new_num++;
						db('goods')->where('goods_id',$id)->update(['uid_num'=>$new_num]);
						db('goods_visit') -> insert(['visit_uid'=>$uid,'goods_id'=>$id]);
						db('goods_visit') -> where(['id'=>$arr['touxiang'][94]['g_v_id']]) ->delete();
						array_unshift($arr['touxiang'],$u);
						$arr['uid_num']++;
					}else{
						$u['url'] = config('URL').'ecshop/images/touxiang/2.png';
						array_unshift($arr['touxiang'],$u);
					}
				}
				$arr['touxiang'] = array_slice($arr['touxiang'],0,95);
				$dian['url'] = config('URL').'ecshop/images/touxiang/dian.png';
				$dian['uid'] = 0;
				array_push($arr['touxiang'],$dian);
			}else{
				if($is_in){
					array_unshift($arr['touxiang'],$u);
				}else{
					if($u['url'] && $uid && $uid!=1 && is_numeric($uid)){
						$new_num = count($arr['touxiang'])+1;
						db('goods')->where('goods_id',$id)->update(['uid_num'=>$new_num]);
						db('goods_visit') -> insert(['visit_uid'=>$uid,'goods_id'=>$id]);
						array_unshift($arr['touxiang'],$u);
						$arr['uid_num']++;
					}else{
						$u['url'] = config('URL').'ecshop/images/touxiang/2.png';
						array_unshift($arr['touxiang'],$u);
					}
				}
			}
		}else{
			if($u['url'] && $uid && $uid!=1 && is_numeric($uid)){
				db('goods')->where('goods_id',$id)->update(['uid_num'=>1]);
				db('goods_visit') -> insert(['visit_uid'=>$uid,'goods_id'=>$id]);
				$arr['touxiang']['0'] = $u;
				$arr['uid_num']++;
			}else{
				$u['url'] = config('URL').'touxiang/2.png';
				array_unshift($arr['touxiang'],$u);
			}
		}
		
		
		
		$arr['img'] = Config('theURL').$arr['img'];// 商品主图
		$arr['desc'] =	str_replace('../',Config('theURL'),$arr['desc']);//商品详情
		$arr['desc'] =	str_replace('&nbsp;','' ,$arr['desc']);//商品详情
		// $arr['bannerimg'] = $this->goodsIdGetGoodsImg($id);// banner图片
		

		$arr['pinglun'] = $this -> DoGoodsPinglun($id,1,5);

		// 去查看规格
		$list = $this -> getgoodsproperties($id);
		$arr['shuxing'] = $list['shuxing']?$list['shuxing']:-1;
		$arr['guige'] = $list['guige']?$list['guige']:-1;

		if($list['guige']){
			$arr['isguige'] = 1;
		}else{
			$arr['isguige'] = 0;
		}
		$arr['kucun'] = $list['kucun']?$list['kucun']:-1;

		// 查看是否收藏
		$arr['iscollect'] = $this ->IsCollectGoods($id,$uid);

		// 查看推荐人
		$tuijian = new DoTuijian;
		$arr['tuijianren'] = $tuijian -> TuijianIdGetTuijianXinxi($arr['tuijianren']);	
		$arr['tuijianren']['img'] = Config('theURL'). $arr['tuijianren']['img'] ;

		$arr['tel'] = '13109575376';
		if($zfid){
			// 查看转发人
			$user = new DoUser();
			$zfarr = $user -> userIdGetUserXinxi($zfid,'user_name,touxiang');


			if($zfarr){
				$arr['zhuanfaren'] = $zfarr;
			}else{
				$arr['zhuanfaren'] = -1;
			}

		}

		if($arr){
			return $arr;
		}
	}




	// 获取评论
	// ID 	商品id
	// page 页数
	// num  数量
	public function DoGoodsPinglun($id,$page,$num){
		$db = db('comment');

		// where
		$where['status'] = ['=','1'];
		$where['id_value'] = ['=',$id]; 

		$limit = ($page-1)*$num;

		$field = ['comment_id'=>'id','content','user_id'];
		$list = $db -> field($field) -> where($where) -> limit($limit,$num) -> select();
		$user = new DoUser();
		$xinxi = "user_name,touxiang";
		foreach ($list as $key => $value) {
			$arr= $user ->userIdGetUserXinxi($list[$key]['user_id'],$xinxi);
			$list[$key]['username'] = $arr['user_name'];
			$list[$key]['usertouxiang'] = $arr['touxiang'];
		}

		if($list){
			return $list;
		}else{
			return -1;
		}
	}



	// 增加评论
	public function DoGoodsPinglunAdd($arr){
		$arr['add_time'] = time();
		$arr['status'] = "1";
		$db = db('comment');

		// 判断60秒之内有没有增加过评论
		$where['user_id']= ['=',$arr['user_id']];
		$where['add_time'] = ['>',($arr['add_time']-60)];
		
		$list = $db ->where($where) -> select();
		if($list){
				return -2;//60秒之内有评论
		}else{
				$add = $db -> insert($arr);
				if($add){
					return 1;//成功
				}else{
					return -1;//失败
				}
		}


	}



	//商品属性的价格。
	public function goodsidGetgoodsShuxingPrice($id){
		$db = db('products');
		$arr = $db ->field(['product_id'=>'id','product_number','goods_attr'])-> where('goods_id','=',$id)->select();
		foreach ($arr as $key => $value) {
			// 规格
			$guige = explode('|',$arr[$key]['goods_attr']);
			$arr[$key]['goods_attr'] = $guige;
		}
		if($arr){
			return $arr;
		}else{
			return 0;
		}
	}






	// 商品的属性
	public function getgoodsproperties($goods_id){
				$arrays = [];
				// $goods_id = 73;
			    /* 对属性进行重新排序和分组 */
			    $sql = "SELECT attr_group FROM jsx_goods_type AS gt, jsx_goods AS g WHERE g.goods_id={$goods_id} AND gt.cat_id=g.goods_type";
			    $sqlarr = Db::query($sql);

			    if($sqlarr){
			    	 $grp = $sqlarr[0]['attr_group'];
			    }
			   
			    if (!empty($grp))
			    {
			        $groups = explode("\n", strtr($grp, "\r", ''));
			    }
			    /* 获得商品的规格 */
			    $sql = "SELECT a.attr_id, a.attr_name, a.attr_group, a.is_linked, a.attr_type, ".
			            "g.goods_attr_id, g.attr_value, g.attr_price " .
			            'FROM jsx_goods_attr AS g ' .
			            'LEFT JOIN jsx_attribute AS a ON a.attr_id = g.attr_id ' .
			            "WHERE g.goods_id = '$goods_id' " .
			            'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';
			    $res = Db::query($sql);


			    $arr['pro'] = array();     // 属性
			    $arr['spe'] = array();     // 规格
			    $arr['lnk'] = array();     // 关联的属性
			    foreach ($res AS $row)
			    {

			        $row['attr_value'] = str_replace("\n", '<br />', $row['attr_value']);

			        if ($row['attr_type'] == 0)
			        {
			            $group = (isset($groups[$row['attr_group']])) ? $groups[$row['attr_group']] : "shuxing";

			            $arr['pro'][$row['attr_id']]['name']  = $row['attr_name'];
			            $arr['pro'][$row['attr_id']]['value'] = $row['attr_value'];
			        }
			        else
			        {
			            $arr['spe'][$row['attr_id']]['attr_type'] = $row['attr_type'];
			            $arr['spe'][$row['attr_id']]['name']     = $row['attr_name'];
			            $arr['spe'][$row['attr_id']]['values'][] = array(

	                            'label'        => $row['attr_value'],
	                            'price'        =>  $row['attr_price'],
	                            'flag'		   => false,
	                            'buzhidao'	   => $row['attr_id'],
	                            'id'           => $row['goods_attr_id']);
			        }

			        if ($row['is_linked'] == 1)
			        {
			            /* 如果该属性需要关联，先保存下来 */
			            $arr['lnk'][$row['attr_id']]['name']  = $row['attr_name'];
			            $arr['lnk'][$row['attr_id']]['value'] = $row['attr_value'];
			        }
			    }

			    $arrays['shuxing'] = $arr['pro'];
		    	$arrays['guige'] = $arr['spe'];
		    	if($arr['spe']){
		    		$arrays['kucun'] = $this -> goodsidGetgoodsShuxingPrice($goods_id);
		    	}else{
		    		$arrays['kucun'] = -1;
		    	}
		    	

		    	return $arrays;
		}



		// 商品的收藏
		//$type   收藏
		//$uid    用户id
		//$gid    商品ID
		public function DoCollect($type,$uid,$gid){

			$arr['user_id']  = $uid;
			$arr['goods_id'] = $gid;
			$db = db('collect_goods');
			$where = ['user_id'=>$arr['user_id'],'goods_id'=>$arr['goods_id']];
			// 收藏
			if($type==1){
				// 判断之前是否有数据
				$arr['add_time'] = time();
				$list = $db -> where($where) -> find();
				if($list){
					return 1;  	//本来就是关注的
				}else{
					$add = $db -> insert($arr);
					if($add){
						return 1;	//成功
					}else{
						return -1;
					}
				}

			}
			// 取消收藏
			else
			{
				$list = $db -> where($where) -> find();
				if($list){
					$del = $db -> where($where) ->delete();
					if($del){
						return 1;	//成功
					}else{
						return -1;   //失败
					}
				}else{
					return 1;
				}


			}
		}

		// 用户是否收藏该商品
		public function IsCollectGoods($id,$uid){
			$db = db('collect_goods');
			$where = ['user_id'=>$uid,'goods_id'=>$id];
			$list = $db -> where($where) ->find();
			if($list){
				return 1;
			}else{
				return -1;
			}
		}



		//通过用户id，查看用户信息
		public function  GoodsIdGetGoodsXinxi($id,$xinxi){
			$db = db('goods');
			$arr = $db -> field($xinxi)->where('goods_id','=',$id) -> find();
			if($arr){
				return $arr;
			}
		}









}
?>