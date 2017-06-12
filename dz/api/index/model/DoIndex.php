<?php 
namespace api\Index\model;

use \think\Model;
use \think\Db;
use \think\Config;

class DoIndex{

	//验证admin信息
	public function doCheck($admin,$pwd,$yzm){
		// return session("verify");exit();
		if(strtolower(trim($yzm)) == session("verify")){
			session("verify",null);
			$re = db('admin') -> where('name',trim($admin)) ->value('password');
			if($re){
				$password = md5(trim($pwd));
				if($re==$password){
					$res = 	db('admin') -> where('name',trim($admin)) ->value('state');
					if($res){
						
						$Re = db('admin') ->where('name',trim($admin)) ->value('id');
						session('admin_id',$Re);
						
						return array('code'=>4);
					}else{
						return array('code'=>3);//管理员停用
					}
				}else{
					return array('code'=>2);//密码错误
				}
			}else{
				return array('code'=>1);//用户不存在
			}
		}else{
			return array('code'=>0);//验证码错
		}
	}

	//地区分类点赞查询数据库信息
	public function doDiQu(){
		$db = db('diqu');
			$list = $db -> field(['cat_id' => 'id','cat_name' => 'cate_name','sort_order','uid_num']) -> where('parent_id','=',0) -> order('sort_order asc') -> select();

			if($list){
				
				foreach ($list as $key => $value) {
					$list2 = $db -> field(['cat_id' => 'id','cat_name' => 'cate_name','uid_num']) -> where('parent_id','=',$list[$key]['id']) -> order('sort_order asc') ->select();
					if(!$value['uid_num']){
						$list[$key]['uid_num'] = 0;
					}
					foreach ($list2 as $k => $v) {
						if(!$v['uid_num']){
							$list2[$k]['uid_num'] = 0;
						}
					}
					$list[$key]['er'] = $list2; 
					$list[$key]['img'] = config('theURl').'images/fenlei/shi_'.$list[$key]['id'].'.jpg'; 
				}
				return $list;
				


			}else{
				return 0;
			}
	}

	//查询地区简介展示页信息
	public function doDiQuJianJie($uid,$did){
		$arr = db('diqu') -> where('cat_id',$did) ->  field(['cat_id','cat_name','cat_desc','banner'=>'xingxiang','biaoqian','uid_num']) -> find();
		$arr['xingxiang'] = config('theURL').$arr['xingxiang'];
		$tx = db('diqu_dianzan') ->field(['id'])->where(['diqu_id'=>['=',$did]])->select();
		$is_in = db('diqu_dianzan') -> where(['diqu_id'=>$did,'visit_uid'=>$uid]) ->value('id');
		$u['uid'] = $uid;
		$u['url'] = db('users') ->where('user_id',$uid)->value('touxiang');
		$arr['touxiang']=array();
		if($tx){
			$arr['touxiang'] = Db::view('diqu_dianzan',['id'=>'g_v_id','visit_uid'=>'uid'])
						->view('users',['touxiang'=>'url'],'diqu_dianzan.visit_uid=users.user_id')
						->where(['diqu_id'=>['=',$did],'visit_uid'=>['<>',$uid]])
						->order('id desc')
						// ->fetchsql()
						->select();

			if(count($arr['touxiang'])>=97){
				if($is_in){
					array_unshift($arr['touxiang'],$u);
					// return $arr['touxiang'];
				}else{
					if($u['url'] && $uid && $uid!=1 && is_numeric($uid)){
						$new_num = db('diqu')->where('cat_id',$did)->value('uid_num');
						$new_num++;
						db('diqu')->where('cat_id',$did)->update(['uid_num'=>$new_num]);
						db('diqu_dianzan') -> insert(['visit_uid'=>$uid,'diqu_id'=>$did]);
						db('diqu_dianzan') -> where(['id'=>$arr['touxiang'][97]['g_v_id']]) ->delete();
						array_unshift($arr['touxiang'],$u);
						$arr['uid_num']++;
					}else{
						$u['url'] = config('URL').'ecshop/images/touxiang/2.png';
						array_unshift($arr['touxiang'],$u);
					}
				}
				$arr['touxiang'] = array_slice($arr['touxiang'],0,98);
				$dian['url'] = config('URL').'ecshop/images/touxiang/dian.png';
				$dian['uid'] = 0;
				array_push($arr['touxiang'],$dian);
			}else{

				if($is_in){
					array_unshift($arr['touxiang'],$u);
				}else{
					if($u['url'] && $uid && $uid!=1 && is_numeric($uid)){
						$new_num = count($arr['touxiang'])+1;
						db('diqu')->where('cat_id',$did)->update(['uid_num'=>$new_num]);
						db('diqu_dianzan') -> insert(['visit_uid'=>$uid,'diqu_id'=>$did]);
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
				db('diqu')->where('cat_id',$did)->update(['uid_num'=>1]);
				db('diqu_dianzan') -> insert(['visit_uid'=>$uid,'diqu_id'=>$did]);
				$arr['touxiang']['0'] = $u;
				$arr['uid_num']++;
			}else{
				$u['url'] = config('URL').'touxiang/2.png';
				array_unshift($arr['touxiang'],$u);
			}
		}


		// $arr['touxiang'] = array();
		// $aa = 1;
		// if(!empty($arr['visit_uid'])){
		// 	$user = db('users') -> where('user_id',$uid) -> value('touxiang');
		// 	$uids = explode(',',$arr['visit_uid']);
		// 	foreach ($uids as $k => $v) {
		// 		$arr['touxiang'][$k] = db('users') -> field(['user_id'=>'uid','touxiang'=>'url'])->where('user_id',$v)->find();
		// 		if(isset($arr['touxiang'][$k]['url'])){ 
		// 			if($arr['touxiang'][$k]['url']==$user){
		// 				$aa = 0;
		// 			}
		// 		}

		// 	}
		// 	if($aa && $uid && $uid!=1 && is_numeric($uid)){
		// 		$new_uid = $arr['visit_uid'].",".$uid;
		// 		$new_num = count($uids) + 1;
		// 		db('diqu')->where('cat_id',$did)->update(['visit_uid'=>$new_uid,'uid_num'=>$new_num]);
		// 		$arr['uid_num'] = $new_num;
		// 		$u['uid'] = $uid;
		// 		$u['url'] = $user;
		// 		$arr['touxiang'][] = $u;
		// 	}
		// 	$arr['touxiang'] = array_reverse($arr['touxiang']);
		// 	$arr['touxiang'] = array_slice($arr['touxiang'],0,100);
		// }else{
		// 	if($uid && $uid!=1 && is_numeric($uid)){
		// 		db('diqu')->where('cat_id',$did)->update(['visit_uid'=>$uid,'uid_num'=>1]);
		// 		$arr['uid_num'] = 1;
		// 		$arr['touxiang']['0']['uid'] = $uid;
		// 		$arr['touxiang']['0']['url'] = db('users') ->where('user_id',$uid)->value('touxiang');
				
		// 	}else{
		// 		$arr['touxiang']['0']['uid'] = 1;
		// 		$arr['touxiang']['0']['url'] = config('URL').'touxiang/2.png';
		// 	}
		// }

		return $arr;
	}


	


}
?>