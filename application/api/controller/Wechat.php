<?php

namespace app\api\controller;


use think\Controller;
//use EasyWeChat\Foundation\Application;
//use mikkle\tp_wechat\WechatApi;

use think\Request;
use think\Db;

class Wechat extends Controller
{


    /**
     * 获取用户授权
     *  /api/wechat/snsapiuserinfo
     */
//
    public function snsapiUserinfo()
    {
        $wx = wxinit();
        //高级用户授权  用户同意 授权后中转到 index 方法中获取用户信息
        $res = $wx->snsapiUserinfo('http://www.yiluzongheng.com/api/Wechat/index');
//        $res = $wx->snsapiUserinfo($redirect_uri);p($wx);

    }

    public function index()
    {
        $wx = wxinit();

        $res = $wx->getOpenid(input());//获取用户openid
        //p($res);die;
        $data = $wx->getUserInfo($res);  //用用户的OPENID 获取 用户详情信息
        if (empty($data)){
            return false;
        }
            $url = $data['headimgurl'];



        //检测用户的信息是否存在
        $dbUser = Db::table('member')->where('openid', $data['openid'])->find();

        //p($dbUser);
        //如果不存在

        if (empty($dbUser)) {

            //图片存放的路径
            $data_time = date('Y-m-d', time());
            $path = "uploads/heads/" . $data_time . "/";

            if (!file_exists($path)) {
                mkdir($path, 0777, true); //创建目录
                chmod($path, 0777); //赋予权限
            }
            //确保图片名唯一，防止重名产生覆盖
            $uniName = 'wx_' . time() . rand(1000, 9000) . '.jpg';

            $res = $this->getImage($url, $path, $uniName);

            //拼接地址保存到数据库
            $headimgurl = '/' . rtrim($res['save_path'], '/');

            $data1 = [
                'openid' => $data['openid'],
                'nickname' => $data['nickname'],
                'headimgurl' => $headimgurl,
                'create_time'=>time()
            ];
            $id = Db::table('member')->insertGetId($data1); //将用户信息存入数据库

            if(!empty($id)){
                $dbUser=Db::table('member')->where('id',$id)->find();
            }

        }
        //查询会员总购物 金额
        $sumprice=db('member')
            ->alias('m')
            ->where('m.id',$dbUser['id'])
            ->join('orders','m_id=m.id','LEFT')
            ->sum('end_price');
        //通过总金额 修改会员  等级
        if($sumprice<=2000 &&$dbUser['level']!=5 ){

            $update['level']=5;
            $update['update_time']=time();
            db('member')->where('id',$dbUser['id'])->update($update);
        }elseif(2000<$sumprice && $sumprice<=5000 &&$dbUser['level']!=4){
            $update['level']=4;
            $update['update_time']=time();
            db('member')->where('id',$dbUser['id'])->update($update);

        }elseif(5000<$sumprice && $sumprice<=10000 &&$dbUser['level']!=3 ){
            $update['level']=3;
            $update['update_time']=time();
            db('member')->where('id',$dbUser['id'])->update($update);

        }elseif(10000<$sumprice&& $sumprice<=15000 &&$dbUser['level']!=2 ){
            $update['level']=2;
            $update['update_time']=time();
            db('member')->where('id',$dbUser['id'])->update($update);
        }elseif(15000<$sumprice && $dbUser['level']!=1){
            $update['level']=1;
            $update['update_time']=time();
            db('member')->where('id',$dbUser['id'])->update($update);

        }


        $dbUser=db('member')->where('openid',$data['openid'])->find();



            //完成登陆
        session('id',$dbUser['id']);
        session('openid',$dbUser['openid']);
        session('nickname',$dbUser['nickname']);

        //完成跳转

        if($dbUser['status']==1) {
            if (!empty(session('url'))) {
                    $url=session('url');
                    session('url',null);
                return redirect($url);
            } else {
            return redirect('/home/index');
        }
        }else{
//            $this->error('禁止访问');
//            apiResponse('400','禁止访问');
            return redirect('/403.html');

        }



    }

    /**
     * Base64 位码图片 字符串
     * /api/wechat/uploads
     */
    public function uploads()
    {

        $file=request()->param();
//        halt($file);
//        file_put_contents('img.txt',var_export($file,true));
        $imgtime = Db::table('member')->where('id', session('id'))->value('imgtime');
        //判断 一定时间 内可以 再次修改头像
        if ($imgtime < strtotime("-1 Minute")){
//       // 设置生成的图片名字
         $uniName = 'wx_' . time() . rand(1000, 9000) . '.jpg';
//        //判断是否有逗号 如果有就截取后半部分
        if (strstr($file['image'],",")){
            $file = explode(',',$file['image']);
            $file = $file[1];
        }

//        设置图片保存路径
        $data_time = date('Ymd', time());
        $path = "uploads/heads/" . $data_time . "/";

// 创建文件
        if (!file_exists($path)) {
            mkdir($path, 0777, true); //创建目录
            chmod($path, 0777); //赋予权限
        }

        //生成图片 返回字节数
        $r=file_put_contents($path.$uniName,base64_decode($file));
//        dump($r);
        //拼接路径和图片名称 保存数据库
        $imgurl = '/' . rtrim($path, '/') .'/'. $uniName;
        //如果 上传成功将路径 保存到数据 库
            if ($r){
                $data = [
                    'headimgurl' => $imgurl,
                    'imgtime' => time()
                ];
                $res = Db::table('member')->where('id',session('id'))->update($data);

                if ($res) {
                    apiResponse(200, '上传成功',$file);
                }
                apiResponse(400, '上传失败');
            }

//
        }else{
            apiResponse(403,'每两周可更改一次头像');

        }

    }
    
    
    /**
     *修改头像 图片文件
     * /api/wechat/upload
     */
    public function upload()
    {
        $file=request()->param();
//        file_put_contents('img.txt',var_export($file,true));

//        session('id',29);
        $file = request()->file('image');

//        $_FILES['Filedata']['name'] = $this->request->post('filename');
        $imgtime = Db::table('member')->where('id', session('id'))->value('imgtime');
        //判断 一定时间 内可以 再次修改头像
        if ($imgtime < strtotime("-1 Minute")){
        // 获取表单上传文件 例如上传了001.jpg


        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {

            //图片存放的路径
            $path = "uploads/heads/";

            $info = $file->move(ROOT_PATH . 'public' . DS . $path);
            //确保图片名唯一，防止重名产生覆盖
//
            $uniName=$info->getSaveName();

            $imgurl = '/' . rtrim($path, '/') .'/'. $uniName;

            if ($info) {
                $data = [
                    'headimgurl' => $imgurl,
                    'imgtime' => time()
                ];
                $res = Db::table('member')->where('id',session('id'))->update($data);

            }

            if ($res) {
                apiResponse(200, '上传成功', $imgurl);
            }
            apiResponse(400, '上传失败');

            }
        }else{
            apiResponse(403,'每两周可更改一次头像');
        }


    }

    /**
     * @param $url
     * @param string $save_dir
     * @param string $filename
     * @return array
     */

//下载远程文件到本地
    public function getImage($url, $save_dir = '', $filename = '')
    {
//        dump($url);
        //根据url获取远程文件
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);

        curl_close($curl);
        //把图片保存到指定目录下的指定文件
        file_put_contents($save_dir . $filename, $res);
//            dump($res);

        return array(
            'file_name' => $filename,
            'save_path' => $save_dir . $filename,
            'error' => 0
        );
    }

}
