<?php

namespace app\api\controller;

use app\api\controller\Base;
use think\Controller;
use    think\Db;
use extend\weixin\demo\example\phpqrcode\QRcode;
use think\Loader;
use think\Request;
use think\Route;

class Native extends Controller
{

    /**
     * Native constructor.
     * 扫码支付
     */
    public function native()
    {
        $request = input();
        $orders_proxy = Db::table('orders')
            ->alias('o')
            ->field('id,goods_name,order_sn_proxy,order_sn,end_price,postage')
            ->where('id', $request['id'])
            ->find();

        //生成朋友代付订单
        if(empty($orders_proxy['order_sn_proxy'])){
            $proxy=$orders_proxy['order_sn'].'proxy';
            $update=[
                'order_sn_proxy'=>$proxy,
            ];
            DB::table('orders')->where('id',$request['id'])->update($update);
        }
        $orders = Db::table('orders')
            ->alias('o')
            ->field('id,goods_name,order_sn_proxy,order_sn,end_price,postage,status')
            ->where('id', $request['id'])
            ->find();

        if ($orders['status'] != 1) {
            $this->success('订单已经支付过了','/home/index');
        }

        $data['goods_name'] = "艺陆纵横-艺术品订单支付";                          //商品名称 商品描述长度128 位
        $data['attach'] = 2;                                            //判断 为 商品支付
        $data['order_sn'] = $orders['order_sn_proxy'];                        //订单号
        $data['total_fee'] = ($orders['end_price'] + $orders['postage']);   //最终出价加上 快递费 测试
//        $data['total_fee'] = ($orders['end_price'] + $orders['postage']) * 100;   //最终出价加上 快递费


        $wx = wxinit();

        $url = $wx->saoma($data);
        $res = $this->qrcode($url);

        $wxdata=getWxConfig();

        $this->assign('data',$data);
        $this->assign('url', $res);
        $this->assign('wxdata', $wxdata);

        return $this->fetch('index');
    }

    public function qrcode($url)
    {
        if (!empty($url)) {


            file_put_contents('url.txt', $url);
            Loader::import('weixin/demo/example/phpqrcode/phpqrcode', EXTEND_PATH);
            error_reporting(E_ERROR);
            $errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H
            $matrixPointSize = "5"; //生成图片大小 ：1到10

            $filename = uniqid() . '.png';
            $path = 'qrcode/' . date('Ymd', time()) . '/';

            // 创建文件
            if (!file_exists($path)) {
                mkdir($path, 0777, true); //创建目录
                chmod($path, 0777); //赋予权限
            }

            $filepath = $path . $filename;
            //生成二维码图片
            QRcode::png($url, $filepath, $errorCorrectionLevel, $matrixPointSize, 2);

            $QR = $filepath;

            $logo = 'http://www.yiluzongheng.com/home/img/gzhlogo.png';

            if ($logo !== FALSE) {
                $QR   = imagecreatefromstring(file_get_contents($QR));
                $logo = imagecreatefromstring(file_get_contents($logo));

                $QR_width = imagesx($QR);//二维码图片宽度
                $QR_height = imagesy($QR);//二维码图片高度
                $logo_width = imagesx($logo);//logo图片宽度
                $logo_height = imagesy($logo);//logo图片高度
                $logo_qr_width = $QR_width / 8;
                $scale = $logo_width / $logo_qr_width;
                $logo_qr_height = $logo_height / $scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;
                //重新组合图片并调整大小
                imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);


            }
            $path = './Public/qrimg/';
            if (!is_dir($path)) {
                $r = mkdir($path, 0777, true);
                if (!$r) {
                    $this->error('创建文件夹失败');
                }
            }

            imagepng($QR, md5(time()) . '.png');
            $file = './' . md5(time()) . '.png'; //旧目录
            $newFile = $path . md5(time()) . '.png';//新目录
            copy($file, $newFile); //拷贝到新目录
            unlink($file); //删除旧目录下的文件

            $path = substr($path, 1);
            $imgsrc = $path . md5(time()) . ".png";

            return $imgsrc;

            exit();

        } else {
            return '参数错误';
        }
    }
}