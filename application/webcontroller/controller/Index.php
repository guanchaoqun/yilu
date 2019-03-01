<?php
/**
 * Created by PhpStorm.
 * User: GUO
 * Date: 2017/12/31
 * Time: 19:24
 */

namespace app\webcontroller\controller;


use think\Cache;

class Index extends Common
{
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {


        return $this->fetch();
    }

    /**
     * 欢迎页
     * @return mixed
     */
    public function content()
    {


        return $this->fetch();
    }

    /**
     * 清除缓存
     */
    public function cleanCache(){
        Cache::clear();
        $this->result('',200,'操作成功');
    }

    /**
     * 权限不足错页面
     * @return mixed
     */
    public function authError(){
        return $this->fetch();
    }

    /**
     * 图标
     * @return mixed
     */
    public function icon(){
        return $this->fetch();
    }
}