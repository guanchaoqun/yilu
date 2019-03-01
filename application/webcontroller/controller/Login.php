<?php
/**
 * Created by PhpStorm.
 * User: GUO
 * Date: 2018/1/1
 * Time: 1:16
 */

namespace app\webcontroller\controller;

use app\common\model\Admin;
use app\common\model\LoginLog;
use think\Controller;
use think\Lang;
use think\Session;

class Login extends Controller
{
    private $model;

    public function _initialize()
    {
        parent::_initialize();
        //      加载多语言
        Lang::load(APP_PATH . $this->request->module() . '/lang/zh-cn/Login.php');

        $this->model=new Admin();
    }

    /**
     * 登录页
     */
    public function index()
    {
        if($this->request->isPost()){
            //获取登录信息
            $data = $this->request->post();

            // 对验证码进行验证
            if (config('login_code_check')) {
                if (!captcha_check($this->request->post('code'))) {
                    $this->result('',400,'验证码错误');
                };
            }

            $user = $this->model->where('username', $data['username'])->find();

            if (!empty($user)) {

                if ($user['status'] != '1') {
                    $this->result('',400,'用户被停用！');
                } else if ($user['password'] != md5(substr(md5($this->request->post('password')), 10, 10) . 'dch')) {
                    $this->result('',400,'密码错误！');
                } else {
                    // 更新登陆信息
                    $user->logins    = $user->logins + 1;
                    $user->last_time = date("Y-m-d H:i:s");
                    $user->last_ip   = $this->request->ip();
                    $user->save();

                    //设置session,cookie
                    session('userId', $user['admin_id']);
                    if (!empty($user['nick_name'])) {
                        cookie('name', $user['nick_name']);
                    } else {
                        cookie('name', $user['username']);
                    }

                    cookie('uname', $user['username'],3600);
                    cookie('uid', $user['admin_id'],3600);
                    //登陆日志
//                    $ipStr = @file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=" . $this->request->ip());   //.$ip


//                    if ($ipStr != '-2') {
//
//                        $llModel   = new LoginLog();
//                        $s         = mb_strpos($ipStr, '{');
//                        $e         = mb_strpos($ipStr, '}');
//                        $ipJsonStr = mb_substr($ipStr, $s, $e - $s + 1);
//                        $ipArr     = json_decode($ipJsonStr, TRUE);
//                        $logData   = [
//                            'uid'      => $user['admin_id'],
//                            'ip'       => $this->request->ip(),
//                            'country'  => $ipArr['country'],
//                            'province' => $ipArr['province'],
//                            'city'     => $ipArr['city'],
//                            'district' => $ipArr['district'],
//                        ];
//                        $llModel->save($logData);
//
//                    }

                    $this->result('',200,'登录成功');
                }
            } else {
                $this->result('',400,'用户名不存在！');
            }

        }

        return $this->fetch();
    }

    /*
 * 后台退出功能
 */
    public function login_out()
    {
        //      清除session（当前作用域）
        Session::clear();
        //      退出成功
        $this->success('退出成功', 'login/index', '', 2);
    }
}