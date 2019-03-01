<?php
namespace app\home\behavior;
use think\Controller;

class CheckLogin extends Controller
{
    // public function run()
    // {
    // 	echo '111';

    public function run()
    {
        if (empty(session('id'))) {
            session('back',$_SERVER['REQUEST_URI']);
//            return $this->error('请先登录 ...','/home/login');
            $this->redirect('/api/wechat/snsapiuserinfo');

        }
    }
}