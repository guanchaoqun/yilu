<?php

namespace app\api\controller;

use think\Controller;
use think\Session;
use think\Db;
use think\loader;

class  Wxpay extends Controller
{
    /**
     * @fun 微信支付  保证金支付
     ** @param  string $openId openid
     * @param  string $goods 商品名称
     * @param  string $attach 附加参数,我们可以选择传递一个参数,比如订单ID
     * @param  string $order_sn 订单号
     * @param  string $total_fee 金额
     * /api/wxpay/pay?id=1&orderclass=1
     */
    public function pay()
    {
        //获取商品id
        $request = input();
//        file_put_contents('pay.txt',$request,FILE_APPEND);
        if (!empty($request['id'])) {
            $bond_order = Db::table('bond_orders')->where('g_id', $request['id'])
                ->where('m_id', session('id'))->find();
            if ($bond_order['status'] != 0) {
                apiResponse('201', '已经支付过了', $bond_order);
            }

            //每次进行支付 对已生成订单 号进行修改 生成 一个新订单号  避免订单 超时
            $i=mt_rand(0,22);
            $str='abcdefghijklmnopqrstuvwxyz';
            $res=$str[$i];

            $update=[
                'order_sn'=>$bond_order['order_sn'].$res
            ];
            $res2=  Db::table('bond_orders')->where('g_id',$request['id'])->where('m_id',session('id'))->update($update);

            $bond_order1=Db::table('bond_orders')->where('g_id',$request['id'])->where('m_id',session('id'))->find();

            $data['openid'] = session('openid');          //用户openid
            $data['goods'] = '艺陆纵横-拍卖保证金';           //商品名称 保证金
            $data['attach'] = 1;                                    // 1 判断支付为 保证金支付
            $data['order_sn'] = $bond_order1['order_sn'];            //保证金的订单号
//            $data['total_fee'] = $bond_order['bond'];               //金额 必须 以INT保存否则报错   测试
            $data['total_fee'] = $bond_order1['bond'] * 100;               //金额 必须 以INT保存否则报错
            $data['g_id'] = $bond_order1['g_id'];

            $wx = wxinit();
            $jsApiParameters = $wx->wxpay($data);
            $this->assign('data', $jsApiParameters);
            $this->assign('res', $data);

            return $this->fetch('index');
        }
    }


    /**
     * 支付回调
     * User:Vernon
     * Date: 2018-01-09
     * @param: xml 微信返回订单xml数据
     * @return: boole
     */
    public function notify()
    {
        $xml = file_get_contents("php://input");
        $data = xmlToArray($xml);
//        file_put_contents('xml.txt',$xml);
//           file_put_contents('43214321.txt',$data['transaction_id']);

//           判断支付状态
        if (($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')) {
//            $result = $data;
            //获取服务器返回的数据
//            $order_sn = $data['out_trade_no'];  //订单单号
//            $order_id = $data['attach'];        //附加参数,选择传递订单ID
//            $openid = $data['openid'];          //付款人openID
//            $total_fee = $data['total_fee'];    //付款金额
            $result = $this->updateOrder($data);
        } else {
            $result = false;
        }
        // 返回状态给微信服务器
        if ($result) {
            $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA]></return_msg></xml>';
        } else {
            $str = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }


    /**
     *
     * 修改订单状态
     * @param $data
     */
    public function updateOrder($data)
    {
        //先判断订单状态 支付回调8次 注意!!!
        //判断  AATTACH 值   为1 的 是修改保证金支付状态   2 为修改商品支付状态
        if (!empty($data['attach']) && $data['attach'] == 1) {

            $order = db('bond_orders')->where('order_sn', $data['out_trade_no'])->find();
            $my_auction = db('my_auction')->where('member_id', session('id'))
                ->where('goods_id', $order['g_id'])
                ->find();
            //修改保证金订单表状态
            if ($order['status'] == 0) {
                //修改保证 金支付状态 为 1
                $update['update_time'] = time();
                $update['status'] = 1;
                $res = db('bond_orders')->where('order_sn', $data['out_trade_no'])->update($update);
                //修改报名表内的保证金状态
                if ($my_auction['bond_status'] == 0) {
                    //支付状态 为  0  未支付
                    //修改保证金支付状态 为1  已支付
                    $up['bond_status'] = 1;
                    $res1 = db('my_auction')->where('goods_id', $order['g_id'])
                        ->where('member_id', $order['m_id'])
                        ->update($up);
                    if ($res && $res1) {return true;} else {return false;}
                }
            }
        }
        //先判断订单状态 支付回调8次 注意!!!
        //判断  AATTACH 值   为1 的 是修改保证金支付状态   2 为修改商品支付状态
        if (!empty($data['attach']) && $data['attach'] == 2) {
            $orders = Db::table('orders')
                ->where('order_sn', $data['out_trade_no'])
//                ->whereOr('order_sn_proxy',$data['out_trade_no'])
                ->find();
            $goods=Db::table('goods')->field('id,auctionstatus')->where('id',$orders['g_id'])->find();

            if(!empty($goods) && $goods['auctionstatus']==3){
                    $up=['auctionstatus'=>4];
                   $res9= Db::table('goods')->where('id',$goods['id'])->update($up);

                }
            if (!empty($orders) && $orders['status'] == 1) {
                //修改支付状态  为已支付    2
                $update['status'] = 2;   //修改为已支付
                $update['pay_time'] = time();
                $res = Db::table('orders')
                    ->where('order_sn', $data['out_trade_no'])
//                    ->whereOr('order_sn_proxy', $data['out_trade_no'])
                    ->update($update);
                if ($res) {return true;} else {return false;}
            }
        }
    }

    /**
     * 定时 任务
     * 拍品订单 已经支付后的会员 保证 金退款
     * /api/wxpay/returnbond
     */
    public function returnbond()
    {

        //商品订单 已经支付过的会员 找到他们的保证金支付信息
        $data = db('orders')->alias('o')
            ->field('o.id as oid,o.status,o.m_id,bond_orders.*')
            ->where('o.status', 'IN', '2,3')
            ->join('bond_orders', 'bond_orders.g_id=o.g_id')
            ->where('bond_orders.status', 1)
            ->where('bond_orders.m_id=o.m_id')
            ->select();

//        file_put_contents('pay1.log',$data,8);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->clreturnbond($v['id']);
            }
        } else {
            return '无保证金订单';
        }

    }

    /**
     * 处理拍品订单 支付成功,会员保证退款
     *
     */
    public function clreturnbond($id)
    {
//        file_put_contents('pay2.log',$id,8);

        $wx = wxinit();

        $data = db('bond_orders')->where('id', $id)->find();
        $data1 = db('orders')->field('status')->where('g_id', $data['g_id'])->find();
//        dump($data);
//        halt($data);
        //订单 没有支付的 不 能退还保证金
        if (!empty($data) && !empty($data1) && $data['status'] == 1 && ($data1['status'] == 2 || $data1['status'] == 3)) {

            $res = $wx->wxRefund($data);
//            file_put_contents('pay11.log',$res,8);

        } else {
            apiResponse('400', '没有保证金订单');
        }
        $data1 = db('my_auction')->where('goods_id', $data['g_id'])->where('member_id', $data['m_id'])->find();
//        dump($data1);
        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {

            if ($data['status'] == 1 && $data1['bond_status'] == 1) {
                $up['status'] = 2;
                $up['bond_refund_time'] = time();
                $update['bond_status'] = 2;
                $update['bond_refundtime'] = time();
                //修改保证金状态 为 2   已退款
//                Db::table('bond_orders')->where('m_id', $data['m_id'])->where('g_id', $data['g_id'])->update($up);
                $a1 = Db::table('bond_orders')->where('id', $id)->update($up);
                $a2 = Db::table('my_auction')->where('goods_id', $data['g_id'])
                    ->where('member_id', $data['m_id'])
                    ->update($update);
                if (!empty($a1) && !empty($a2)) {
                    return '修改成功';

                } else {
                    return '修改失败';
                }
            }
            echo '退款成功';
        } else {
            echo '退款失败';
        }
    }

    /**
     * 微信退款未获拍的用户
     *  /api/wxpay/weihuopai
     */
    public function weihuopai()
    {
        $data1 = db('goods')->field('id')->where('status', 1)
            ->where('auctionstatus', 'IN', '2,3,4')
            ->select();
        if (!empty($data1)) {
            foreach ($data1 as $k => $v) {
                //查找出商品订单的MID  GID
                $data = db('orders')->field('g_id,m_id')->where('g_id', $v['id'])->find();

                if (!empty($data)) {
                    $gid = $data['g_id'];
                    $mid = $data['m_id'];
                    $this->chuliwhp($gid, $mid);
//                    echo '有生成订单';
                } else {
                    echo '无订单';
                }
            }
        } else {
            echo '无';
        }

    }

    /**
     * 通过 订单中的M_ID 查找未获拍的用户的报名信息
     * /api/wxpay/chuliwhp
     */
    public function chuliwhp($gid, $mid)
    {
        $data = db('bond_orders')
            ->where('m_id', '<>', $mid)
            ->where('g_id', $gid)
            ->where('status', 1)
            ->select();
//        dump($data);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->fund($v);
            }
            echo '有未获拍用户保证金订单 ';
        } else {
            echo '无未获拍用户保证金订单 ';
        }
    }

    /**
     * 退款
     */
    public function fund($v)
    {
        if (empty($v)) {
            return false;
        }
        $wx = wxinit();

        $res = $wx->wxRefund($v);
        //修改保证金状态
        $data1 = db('my_auction')->where('goods_id', $v['g_id'])->where('member_id', $v['m_id'])->find();

        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
            if ($v['status'] == 1 && $data1['bond_status'] == 1) {
                $up['status'] = 2;
                $up['bond_refund_time'] = time();
                $update['bond_status'] = 2;
                //修改保证金状态 为 2   已退款
                $a1 = Db::table('my_auction')->where('member_id', $v['m_id'])->where('goods_id', $v['g_id'])->update($update);

                $a2 = Db::table('bond_orders')->where('m_id', $v['m_id'])->where('g_id', $v['g_id'])->update($up);
                if ($a1 && $a2) {
                    echo '修改成功';
                } else {
                    echo '修改失败';
                }

            }
            echo '未获拍用户保证金退款成功';

        } else {
            echo '未获拍用户保证金退款失败';
        }
    }

    /**
     * 流拍的商品保证金退款
     *  /api/wxpay/liupaibond
     */
    public function liupaibond()
    {
        $data = Db::table('goods')
            ->alias('g')
            ->field('g.id,g.auctionstatus,g.status as gstatus,b.status,b.g_id,b.bond,b.goods_name,b.order_sn,b.m_id')
            ->where('auctionstatus', 9)
            ->where('g.status', 1)
            ->join('bond_orders b', 'b.g_id=g.id')
            ->where('b.status', 1)
            ->select();

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->liupai($v);
            }
        }

    }

    /**
     * 流拍的商品是否有人报名交保证金 如果有人交保证金 退回保证金
     */
    public function liupai($v)
    {
        if (!empty($v)) {

            $wx = wxinit();

            $res = $wx->wxRefund($v);
            //修改保证金状态
            $data1 = db('my_auction')->where('goods_id', $v['g_id'])->where('member_id', $v['m_id'])->find();

            if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
                if ($v['status'] == 1 && $data1['bond_status'] == 1) {
                    $up['status'] = 2;
                    $up['bond_refund_time'] = time();
                    $update['bond_status'] = 2;
                    //修改保证金状态 为 2   已退款
                    $a1 = Db::table('my_auction')->where('member_id', $v['m_id'])->where('goods_id', $v['g_id'])->update($update);

                    $a2 = Db::table('bond_orders')->where('m_id', $v['m_id'])->where('g_id', $v['g_id'])->update($up);
                    if ($a1 && $a2) {
                        echo  '修改成功';
                    } else {
                        echo   '修改失败';
                    }

                }
                echo  '流拍商品用户保证金退款成功';

            } else {
                echo '流拍商品用户保证金退款失败';
            }

        } else {
            return '无';
        }

    }
}