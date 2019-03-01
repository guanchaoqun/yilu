<?php

namespace app\api\controller;

use think\Controller;
use think\Db;

class Goodspay extends Base
{
    /**
     * @return mixed
     * @throws 商品支付
     * @throws /api/goodspay/index
     * @throws
     */
    public function index()
    {
        $request = input();
        $orders = Db::table('orders')
            ->alias('o')
//            ->join('goods','goods.id=o.g_id')
            ->where('id', $request['id'])
            ->find();

       $goods=Db::table('goods')->where('id',$orders['g_id'])->find();


        if ($orders['status'] != 1) {
            $this->success('订单已经支付过了','/home/index','',0);
        }

        $i=mt_rand(0,22);
        $str='abcdefghijklmnopqrstuvwxyz';
        $res=$str[$i];

        $update=[
            'order_sn'=>$orders['order_sn'].$res
        ];
        DB::table('orders')->where('id',$request['id'])->update($update);
        $orders1=Db::table('orders')->where('id',$request['id'])->find();

        $data['openid'] = session('openid');                        //openid
//        $data['goods'] = $orders['goods_name'];                          //商品名称 商品描述长度128 位
        $data['goods'] = "艺陆纵横-艺术品订单支付";                          //商品名称 商品描述长度128 位
        $data['attach'] = 2;                                            //判断 为 商品支付
        $data['order_sn'] = $orders1['order_sn'];                        //订单号
//        $data['total_fee'] = ($orders['end_price'] + $orders['postage']);   //最终出价加上 快递费 测试
        $data['total_fee'] = ($orders1['end_price'] + $orders1['postage']) * 100;   //最终出价加上 快递费

        $wx = wxinit();
        $wxdata = getWxConfig();

        $jsApiParameters = $wx->wxpay($data);
        $this->assign('wxdata',$wxdata);
        $this->assign('goods',$goods);
        $this->assign('res', $orders1);
        $this->assign('data', $jsApiParameters);

        return $this->fetch('index');

    }
}