<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return 1;
    }
    public function text(){
        $arr = db('forum_forum')->select();
        p($arr);
    }
    public function a(){
       return 1;
    }
    
}
