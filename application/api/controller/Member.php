<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class member extends Base
{

    public function xiugai()
    {
        return view('xiugai');
    }
    /**
     * 用户个人中心 渲染视图
     */
    public function index()
    {
            return view('my');
    }

    /**
     * @return \think\response\View
     * 渲染视图  修改手机 页面
     */
    public function modifyPhone()
    {
        return view('modifyPhone');
    }

    /**
     * @return \think\response\View
     * 绑定 手机
     */
    public function  onPhoneNum()
    {
        return view('onPhoneNum');
    }
    /**
     * 关注大师
     */
    public function focusMaster()
    {
        return view('focusMaster');
    }
    /**
     * 获取用户信息
     * /api/member/memberInfo?m_id=3
     */
    public function memberInfo()
    {


        $data = Db::table('member')
             ->field('id,nickname,headimgurl,phone,level')
             ->where('id',session('id'))
             ->find();
        if (!empty($data)) {
            $arr[] = $data;
            apiResponse(200,'请求成功',$arr);
        }else{
            apiResponse(400,'请求失败');
        }
    }
}
