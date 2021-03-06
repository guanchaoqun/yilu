<?php
/**
 * 基础类
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 17:46
 */

namespace app\golf\controller;


use think\Controller;
use think\Lang;

class Base extends Controller
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        define('MODULE_NAME', $this->request->module());
        define('CONTROLLER_NAME', $this->request->controller());
        define('ACTION_NAME', $this->request->action());

        //加载多语言
        Lang::load(APP_PATH . MODULE_NAME. '/lang/zh-cn/' . CONTROLLER_NAME . '.php');
    }
}