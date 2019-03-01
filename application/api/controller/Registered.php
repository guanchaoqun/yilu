<?php

namespace app\api\controller;

use app\api\controller\Base;
use EasyWeChat\Payment\API;
use think\Controller;
use think\Loader;
use think\Db;

//use extend\aliyun\aip_demo\SmsDemo;
class Registered extends Base
{

    public function index()
    {
//
    }

    /**
     * @function 调用aliyun 发送手机验证码
     * @return int
     * @接口地址 http://auction.jiangliping.com/api/registered/send_dx
     * @type   POST
     * @return  json
     */
    public function send_dx()
    {
        // 加载extend文件夹里面的文件
        Loader::import('aliyun.api_demo.SmsDemo', EXTEND_PATH);

        $phone = input('post.phone');

        if (!empty($phone)) {
            $code = rand(100000, 999999);
            // 调用示例：
            set_time_limit(0);
            header('Content-Type: text/plain; charset=utf-8');
            $response = (new \SmsDemo)::sendSms($phone, $code);
            //$response = (new \SmsDemo)::sendBatchSms();
            $response = (new \SmsDemo)::querySendDetails($phone);
            // 保存验证码
            session('code', $code);
//            dump($code);

            apiResponse(200, '获取验证码成功',$code);
        } else {
            apiResponse(400, '获取验证码失败');
        }
    }

    /**
     * @function 绑定手机
     * @type    post
     * @return  json
     * @参数   tel   yzm
     * @接口地址   api/registered/enrol
     */
    public function enrol()
    {
        $phone = input('post.phone');
        $yzm = input('post.yzm');
         //session('id','11');
        if (!empty($phone) && !empty($yzm)) {
            //效验验证码
            if ($this->check_dx($yzm)) {
                $res = Db::table('member')->where('id', session('id'))->update(['phone' => $phone]);
            }
            if (!empty($res)) {
                apiResponse(200, '绑定成功',$phone);

            }
            apiResponse(400, '绑定失败');
        }


    }

    /**
     * 机修改页面
     *  /api/registered/viewphone
     */
    public function viewPhone()
    {
//          session('id',29);//临时

          //session('id',null);
        if (session('id')) {
            $phone = Db::table('member')->field('phone')->where('id', session('id'))->find();
        } else {
            return redirect(url('/api/wechat/snsapiUserinfo'));
        }
        if (!empty($phone)) {
            apiResponse(200, '获取成功', $phone);
        }
        apiResponse(400, '获取失败',$phone);

    }

    /**
     * 效验验证码
     * return    true false
     *  api/registered/code
     * 验证成功后跳转到手机修改页面
     */
    public function code()
    {
        //$res = Db::table('member')->where('id', session('id'))->field('phone')->find();

        if ($this->check_dx(input('yzm'))) {
            apiResponse(200, '验证成功');
        }
        apiResponse(400, '验证失败');
    }

    /**
     * 验证接收到的手机验证码
     * @return int|string
     * @接口地址 api/registered/check_dx
     * @type    post
     */
    public function check_dx($yzm)
    {
         //$yzm = input('post.yzm');
        if ($yzm == session('code')) {
//            session('code', null);
           //apiResponse(200, '验证成功');
            return true;
        }
        return false;
    }

}