<?php
    namespace app\index\controller;
    use think\View;
    use think\Controller;
    use think\Db;
    use think\Config;
    class Zhan{
        private function addPar($fup){
            $arr = db('jsx_forum_forum')->where(['status'=>1,'fup'=>$fup])->field('name,fid')->select();
                foreach ($arr as $k =>$v){
                    $xinArr = self::addPar($v['fid']);
                    if(count($xinArr)!= 0){
                        $arr[$k]['str'] = $xinArr;
                    }
                }           
            return $arr;           
        }
        public function index(){
            $arr = self::addPar(0);
            p($arr);
        }        
    }
?>