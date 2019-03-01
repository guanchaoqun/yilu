<?php
namespace app\webcontroller\controller;

use think\Db;

class header extends Common
{

    public function index()
    {

        $res = db('orders')->where('status', 2)->where('remind', 1)->sum();


    }


}