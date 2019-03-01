<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class Orders extends Base
{
    /**
     * 提醒 发货
     * /api/orders/remind
     */
    public function remind(Request $request)
    {
        $o_id = $request->param();

        $res = Db::table('orders')->field('id,remind,status')->where('id', $o_id['id'])->find();
        if (!empty($res) && $res['remind'] != 1 &&$res['status']==2) {
            $up = ['remind' => 1];
            $res1 = Db::table('orders')->where('id', $o_id['id'])->update($up);
            if (!empty($res1)) {
                apiResponse('200', '提醒成功');
            } else {
                apiResponse('400', '提醒失败');
            }
        } else {
            apiResponse('220', '已提醒卖家发货');
        }

    }

    /**
     * 等待开始的商品 状态 为 0
     * /api/orders/ddks
     */
    public function ddks()
    {
        //查询即将开始的拍品修改状态
        $readygoods = Db::table('goods')
            ->field('id,goods_name,auctionstatus')
            ->whereTime('start_time', '>', date('Y-m-d H:i:s', time()))
            ->select();
        if (!empty($readygoods)) {
            foreach ($readygoods as $k => $v) {
                if (!empty($v) && $v['auctionstatus'] != 0) {
                    $up = ['auctionstatus' => 0];
                    $res1 = Db::table('goods')->where('id', $v['id'])->update($up);
                }
            }
            if (!empty($res1)) {
                return '修改成功 ';
            } else {
                return '没修改';
            }

        }
    }

    /**
     * 处理即将开始的拍品  计划任务  开始拍品修改状态
     * /api/orders/kaishi
     */
    public function kaishi()
    {

        //查询已经开始的拍品
//        file_put_contents('kaishi.txt','3',FILE_APPEND);

        $startgoods = Db::table('goods')
            ->field('id,goods_name,auctionstatus')
            ->where('auctionstatus', 0)
            ->whereTime('start_time', '<', date('Y-m-d H:i:s', time()))
            ->whereTime('end_time', '>', date('Y-m-d H:i:s', time()))
            ->select();
        if (!empty($startgoods)) {
            foreach ($startgoods as $k => $v) {
                if (!empty($v) && $v['auctionstatus'] == 0) {
                    $update = ['auctionstatus' => 1];
                    $res = Db::table('goods')->where('id', $v['id'])->update($update);
                }
            }
            if (!empty($res)) {
                return '修改成功';
            } else {
                return '未修改';
            }
        }

    }

    /**
     * 处理已经到时的拍品 计划任务 结束拍品修改状态
     *
     *  /api/orders/jieshu
     */
    public function jieshu()
    {

//        file_put_contents('jieshu.txt', '2', FILE_APPEND);
        //查询 已经结束的商品
//        $endgoods = Db::table('goods')->whereTime('end_time', '<', date('Y-m-d H:i:s', time()))->select();
        $endgoods = Db::table('goods')
            ->field('id,goods_name,auctionstatus')
            ->where('auctionstatus', '1')
            ->whereTime('end_time', '<', date('Y-m-d H:i:s', time()))
            ->select();
        if (!empty($endgoods)) {

            foreach ($endgoods as $k => $v) {
                if (!empty($v) && $v['auctionstatus'] == 1) {

                    $update = ['auctionstatus' => 2];

                    $res = Db::table('goods')->where('id', $v['id'])->update($update);
                }
            }
            if (!empty($res)) {
                return '修改';
            } else {
                return '未修改';
            }

        }

    }


    /**
     * 生成订单
     *订执行
     * /api/orders/aa
     */
    public function aa()
    {
        //已经结束所有拍卖的商品
        $goodscount = Db::table('goods')
            ->field('id,goods_name,cover_plan,bond,postage,artist_id,end_time,auctionstatus')
            ->where('status', 1)
//            ->whereTime('end_time', '<', time())
            ->where('auctionstatus', 2)
            ->select();


        if (!empty($goodscount)) {
            $n = count($goodscount);

            if ($n > 0) {
                foreach ($goodscount as $k => $v) {

                    $data = $this->dingdan($v['id']);  //调用生成订单方法
//                    dump($data);
                }

            }
        } else {
            return '无结束商品';
        }
        //查询 总条数据 通过 总数做判断 ,有几天就执行几次程序每执行一次调用 一次方法  减少一次数量

    }

    /**
     * 生成订单
     *
     */
    public function dingdan($id)
    {
//        dump($id);
        //查询出单条商品信息
        $goods = Db::table('goods')->field('id,goods_name,cover_plan,bond,postage,artist_id,end_time')->where('id', $id)->find();
        //查询是否有出价
        $off = Db::table('goods_offer')->where('goods_id', $id)->select();
        //查询是否有订单
        $order = Db::table('orders')->where('g_id', $id)->find();

        if (time() < strtotime($goods['end_time'])) {
            apiResponse(400, '拍卖未结束不能生成订单');
        }
        //判断如果 没有出价 修改商品状态已流拍
        if (empty($off)) {
            //流拍状态   9
            Db::table('goods')->where('id', $goods['id'])->update(['auctionstatus' => 9]);
            return '该商品无人出价';
        }
        if (!empty($order)) {
            //已有订单 修改状态 3
            Db::table('goods')->where('id', $goods['id'])->update(['auctionstatus' => 3]);
            return '商品已有订单';

        }
        //获取商品的最高价
        $goods_offer = Db::table('goods_offer')->where('goods_id', $id)->order('price desc')->find();
        $goods['price'] = $goods_offer['price'];
        $goods['member_id'] = $goods_offer['member_id'];
        //获取此商品属于哪个店铺
        $shop = Db::table('shop')->where('art_id', $goods['artist_id'])->find();
        $goods['s_id'] = $shop['id'];
        $goods['shop'] = $shop['shopname'];
        //生成订单号
        //首先根据 商店表里面的艺术家ID 查找 此商品属于哪个大师
        $artist = Db::table('artist')->where('id', $shop['art_id'])->find();
        //然后根据大师表里面的ID 查找 此大师是艺术家还是收藏家
        $artist_class = Db::table('artist_class')->where('id', $artist['artist_class_id'])->find();
        $goods['artist'] = $artist['name'];
        $goods['artist_class'] = $artist_class['name'];
        //①生成订单号 - 收藏家/艺术家的首字母(大写)
        $name_words = "";
        if ($artist_class['name']) {
            for ($i = 0; $i < strlen($artist_class['name']); $i = $i + 3) {
                $name_words .= _getFirstCharter(substr($artist_class['name'], $i, 3));
            }
            // echo $name_words;
        }
        //②8位时间
        $time = date('Ymd', time());
        //③当天第几单,例如第一单显示001
        $start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $stop_time = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

        $num = Db::table('orders')
            ->where('create_time', '>', $start_time)
            ->where('create_time', '<', $stop_time)
            ->count();

        if (100 > $num && $num > 10) {
            $num = $name_words . $time . '0' . ($num + 1);
        } else if ($num < 10) {
            $num = $name_words . $time . '00' . ($num + 1);
        } else {
            $num = $name_words . $time . ($num + 1);
        }

        $goods['order_sn'] = $num;

        //获取此用户的默认地址
        $address = Db::table('address')
            ->where('m_id', $goods_offer['member_id'])
            ->where('state', 1)
            ->find();
        if (empty($address)) {
            apiResponse(400, '请先添加地址');
        }

        $goods['address_name'] = $address['name'];;
        $goods['address_phone'] = $address['phone'];;
        $goods['address'] = $address['area'] . $address['address'];

        // p($goods);
        //判断一件商品只能添加一次订单
        $one = Db::table('orders')
            ->where('g_id', $id)
            ->select();

        if (empty($one)) {
            //生成订单数据-添加到数据库
            $arr = [
                'm_id' => $goods['member_id'],
                'g_id' => $goods['id'],
                's_id' => $goods['s_id'],
                'goods_name' => $goods['goods_name'],
                'cover_plan' => $goods['cover_plan'],
                'order_sn' => $goods['order_sn'],
                'name' => $goods['address_name'],
                'phone' => $goods['address_phone'],
                'address' => $goods['address'],
                'end_price' => $goods['price'],
                'shop' => $goods['shop'],
                'postage' => $goods['postage'],
                'create_time' => time()
            ];
            $add = Db::table('orders')
                ->insert($arr);
//        dump($add);

            if (!empty($add)) {
                apiResponse(200, '添加订单成功');
            } else {
                apiResponse(400, '添加订单失败');
            }
        } else {
            apiResponse(400, '订单已存在 查看订单');
        }
    }


    /**
     * 生成商品订单  前台倒计时 当等于商品结束时间 时 请求此方法
     * 生成订单
     * /api/orders/addorders?g_id=1
     *
     */
    public function addOrders(Request $request)
    {
        $param = $request->param();

        //根据商品ID获取商品信息
        $goods = Db::table('goods')
            ->field('id,goods_name,cover_plan,bond,postage,artist_id,end_time')
            ->where('id', $param['g_id'])
            ->find();

        if (time() < strtotime($goods['end_time'])) {
            apiResponse(400, '拍卖未结束不能生成订单');
        }

        //获取商品的最高价
        $goods_offer = Db::table('goods_offer')
            ->where('goods_id', $param['g_id'])
            ->order('price desc')
            ->find();
        $goods['price'] = $goods_offer['price'];
        $goods['member_id'] = $goods_offer['member_id'];
//       dump($goods['member_id']);
        //获取此商品属于哪个店铺
        $shop = Db::table('shop')
            ->where('art_id', $goods['artist_id'])
            ->find();
        $goods['s_id'] = $shop['id'];
        $goods['shop'] = $shop['shopname'];

        //生成订单号
        //首先根据 商店表里面的艺术家ID 查找 此商品属于哪个大师
        $artist = Db::table('artist')
            ->where('id', $shop['art_id'])
            ->find();
        //然后根据大师表里面的ID 查找 此大师是艺术家还是收藏家
        $artist_class = Db::table('artist_class')
            ->where('id', $artist['artist_class_id'])
            ->find();
        $goods['artist'] = $artist['name'];
        $goods['artist_class'] = $artist_class['name'];
        //①生成订单号 - 收藏家/艺术家的首字母(大写)
        $name_words = "";
        if ($artist_class['name']) {
            for ($i = 0; $i < strlen($artist_class['name']); $i = $i + 3) {
                $name_words .= _getFirstCharter(substr($artist_class['name'], $i, 3));
            }

        }
        //②8位时间
        $time = date('Ymd', time());
        //③当天第几单,例如第一单显示001
        $start_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $stop_time = mktime(23, 59, 59, date('m'), date('d'), date('Y'));

        $num = Db::table('orders')
            ->where('create_time', '>', $start_time)
            ->where('create_time', '<', $stop_time)
            ->count();

        if (100 > $num && $num > 10) {
            $num = $name_words . $time . '0' . ($num + 1);
        } else if ($num < 10) {
            $num = $name_words . $time . '00' . ($num + 1);
        } else {
            $num = $name_words . $time . ($num + 1);
        }

        $goods['order_sn'] = $num;

        //获取此用户的默认地址
        $address = Db::table('address')
            ->where('m_id', $goods_offer['member_id'])
            ->where('state', 1)
            ->find();
        if (empty($address)) {
            apiResponse(400, '请先添加地址');
        }

        $goods['address_name'] = $address['name'];;
        $goods['address_phone'] = $address['phone'];;
        $goods['address'] = $address['area'] . $address['address'];

        // p($goods);
        //判断一件商品只能添加一次订单
        $one = Db::table('orders')
            ->where('g_id', $param['g_id'])
            ->select();

        if (empty($one)) {
            //生成订单数据-添加到数据库
            $arr = [
                'm_id' => $goods['member_id'],
                'g_id' => $goods['id'],
                's_id' => $goods['s_id'],
                'goods_name' => $goods['goods_name'],
                'cover_plan' => $goods['cover_plan'],
                'order_sn' => $goods['order_sn'],
                'name' => $goods['address_name'],
                'phone' => $goods['address_phone'],
                'address' => $goods['address'],
                'end_price' => $goods['price'],
                'shop' => $goods['shop'],
                'postage' => $goods['postage'],
                'create_time' => time()
            ];

            $add = Db::table('orders')
                ->insert($arr);


            if (!empty($goods)) {
                apiResponse(200, '添加订单成功');
            } else {
                apiResponse(400, '添加订单失败');
            }
        } else {
            apiResponse(400, '订单已存在 查看订单');
        }

    }

    /**
     * 订单 渲染视图
     */
    public function order()
    {
        return view('order');
    }

    /**
     * 我的订单 - 全部订单页
     * /api/orders/ordersList?m_id=3&status=
     * 会员ID : m_id
     * 订单状态 : status
     */

    public function ordersList(Request $request)
    {
        $param = $request->param();
//        session('id', 11);
        if (!empty($param['status']) && $param['status'] == 1 || $param['status'] == 2 || $param['status'] == 3 || $param['status'] == 4 || $param['status'] == 5) {
            $order = Db::table('orders')
                ->alias('o')
                ->field('o.id,o.m_id,o.g_id,o.s_id,o.shop,goods.goods_name,o.cover_plan,starting_price,end_price,o.postage,o.status,o.create_time,bond,pay_time')
                ->join('goods', 'o.g_id = goods.id', 'LEFT')
//                ->where('m_id', $param['m_id'])
                ->where('m_id', session('id'))
                ->where('o.status', $param['status'])
                ->where('o.m_id', session('id'))
                ->order('o.create_time desc')
                ->select();
        } else {
            $order = Db::table('orders')
                ->alias('o')
                ->field('o.id,o.m_id,o.g_id,o.s_id,o.shop,goods.goods_name,o.cover_plan,o.end_price,starting_price,o.postage,o.status,o.create_time,bond,pay_time')
                ->join('goods', 'o.g_id = goods.id', 'LEFT')
                ->where('m_id', session('id'))
                ->where('o.m_id', session('id'))
                ->order('o.create_time desc')
                ->select();
//            dump($order);
        }

        foreach ($order as $k => $v) {
            $my_auction = Db::table('my_auction')
                ->where('member_id', $v['m_id'])
                ->where('goods_id', $v['g_id'])
                ->column('bond_status');

            foreach ($my_auction as $k1 => $v1) {
                $v['bond_status'] = $v1;
                $order[$k]['bond_status'] = $v['bond_status'];
            }


            if ($v['status'] == 1) {
                $order[$k]['info_url'] = 'http://www.yiluzongheng.com/api/orders/daiFukuan?o_id=' . $v['id'];
                $order[$k]['status_name'] = '等待买家付款';
                //生成订单6小时内付款
                $order[$k]['down_time'] = $v['create_time'] + 6 * 60 * 60;
            } elseif ($v['status'] == 2) {
                $order[$k]['info_url'] = 'http://www.yiluzongheng.com/api/orders/daiFahuo?o_id=' . $v['id'];
                $order[$k]['status_name'] = '等待卖家发货';
            } elseif ($v['status'] == 3) {
                $order[$k]['info_url'] = 'http://www.yiluzongheng.com/api/orders/daiShowhuo?o_id=' . $v['id'];
                $order[$k]['status_name'] = '等待买家收货';
            } elseif ($v['status'] == 4) {
                $order[$k]['info_url'] = 'http://www.yiluzongheng.com/api/orders/yiWancheng?o_id=' . $v['id'];
                $order[$k]['status_name'] = '交易完成';
            } elseif ($v['status'] == 5) {
                $order[$k]['info_url'] = 'http://www.yiluzongheng.com/api/orders/weiYue?o_id=' . $v['id'];
                $order[$k]['status_name'] = '已超时买家未付款';
            }
        }

//         p($order);
        if (!empty($order)) {

            apiResponse(200, '请求成功', $order);
        } else {
            apiResponse(400, '无记录');
        }

    }

    /**
     * 待付款 渲染视图
     *
     */
    public function daifukuans()
    {
        return view('daifukaun');
    }

    /**
     * 订单 详情页 渲染视图
     */
    public function orderdetails()
    {
        return view('orderDetails');
    }

    /**
     * 我的订单 - 订单详情页 - 待付款
     * /api/orders/daiFukuan?o_id=1
     */
    public function daiFukuan(Request $request)
    {
        $param = $request->param();
//        session('id', 11);
        $order = Db::table('orders')
            ->field('orders.id,orders.m_id,orders.g_id,orders.s_id,orders.order_sn,orders.shop,goods.goods_name,orders.cover_plan,orders.end_price,orders.postage,orders.status,orders.create_time,bond,end_time as deal_time,orders.name,orders.address,orders.phone,starting_price,pay_time')
            ->join('goods', 'orders.g_id = goods.id', 'LEFT')
            ->where('orders.id', $param['o_id'])
            ->where('orders.status', 1)
            ->where('orders.m_id', session('id'))
            ->find();

        // p($order);
        if (!empty($order)) {
            $my_auction = Db::table('my_auction')
                ->where('member_id', $order['m_id'])
                ->where('goods_id', $order['g_id'])
                ->find();

            $order['bond_status'] = $my_auction['bond_status'];
            apiResponse(200, '请求成功', array($order));
        } else {
            apiResponse(400, '无记录');
        }

    }

    /**
     * 待发货渲染视图
     */
    public function daifahuos()
    {
        return view('daifahuo');
    }

    /**
     * 我的订单  - 订单 详情页  - 特发货
     * /api/orders/daifahuo?o_id=
     */
    public function daiFahuo(Request $request)
    {
//        session('id', 11);
        $param = $request->param();
        $order = Db::table('orders')
            ->alias('o')
            ->field('o.id,o.m_id,o.g_id,o.s_id,o.order_sn,o.shop,o.goods_name,o.cover_plan,o.end_price,o.postage,o.create_time,bond,end_time as deal_time,o.address,o.phone,starting_price,pay_time')
            ->join('goods', 'goods.id=o.g_id', 'LEFT')
            ->where('o.id', $param['o_id'])
            ->where('o.status', 2)
            ->where('o.m_id', session('id'))
            ->find();
        if (!empty($order)) {
            apiResponse('200', '获取成功', array($order));
        } else {
            apiResponse('400', '获取失败');
        }


    }

    /**
     *  代收货页面 渲染视图
     */
    public function daishouhuos()
    {
        return view('daishouhuo');
    }

    /**
     * 我的订单 - 订单详情页 - 待收货
     * /api/orders/daiShouhuo?o_id=1
     */
    public function daiShouhuo(Request $request)
    {
//        session('id', 11);
        $param = $request->param();

        $order = Db::table('orders')
            ->field('orders.id,orders.m_id,orders.g_id,s_id,orders.order_sn,orders.shop,orders.goods_name,orders.cover_plan,orders.end_price,orders.postage,orders.status,orders.create_time,orders.logistics,orders.logistics_number,orders.logistics_time,bond,end_time as deal_time,orders.name,orders.address,orders.phone,pay_time,starting_price')
            ->join('goods', 'orders.g_id = goods.id', 'LEFT')
            ->where('orders.id', $param['o_id'])
            ->where('orders.status', 3)
            ->where('orders.m_id', session('id'))
            ->find();


        if (!empty($order)) {
            $my_auction = Db::table('my_auction')
                ->where('member_id', $order['m_id'])
                ->where('goods_id', $order['g_id'])
                ->find();

            $order['bond_status'] = $my_auction['bond_status'];
            apiResponse(200, '请求成功', array($order));
        } else {
            apiResponse(400, '无记录');
        }
    }

    /**
     * 我的订单 - 订单详情页 - 确认收货
     * /api/orders/shouHuo?o_id=7
     */
    public function shouHuo(Request $request)
    {
        $param = $request->param();
//        file_put_contents('11.txt',$param);
//        halt($param);
        $order = Db::table('orders')
            ->where('id', $param['o_id'])
            ->update(['status' => 4, 'confirm_time' => time()]);
        if (!empty($order)) {
            apiResponse(200, '确认收货成功', $order);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 我的订单 - 订单详情页 - 已完成
     * /api/orders/yiWancheng?o_id=7
     */
    public function yiWancheng(Request $request)
    {
        $param = $request->param();

        $order = Db::table('orders')
            ->field('orders.id,orders.m_id,orders.g_id,s_id,orders.shop,orders.goods_name,orders.cover_plan,orders.end_price,orders.postage,orders.status,orders.create_time,orders.logistics,orders.logistics_number,bond,orders.name,orders.address,orders.phone,orders.order_sn,end_time as deal_time,pay_time,orders.logistics_time,orders.confirm_time,starting_price')
            ->join('goods', 'orders.g_id = goods.id', 'LEFT')
            ->join('my_auction', 'orders.g_id = my_auction.goods_id', 'LEFT')
            ->where('orders.id', $param['o_id'])
            ->where('orders.status', 4)
            ->find();

//        if ($order['logistics'] == 1) {
//            $order['logistics'] = '顺丰速运';
//        } elseif ($order['logistics'] == 2) {
//            $order['logistics'] = '申通快递';
//        } elseif ($order['logistics'] == 3) {
//            $order['logistics'] = '圆通快递';
//        } elseif ($order['logistics'] == 4) {
//            $order['logistics'] = '天天快递';
//        } elseif ($order['logistics'] == 5) {
//            $order['logistics'] = '韵达快递';
//        } elseif ($order['logistics'] == 6) {
//            $order['logistics'] = '中通快递';
//        } elseif ($order['logistics'] == 7) {
//            $order['logistics'] = '百世汇通';
//        } elseif ($order['logistics'] == 8) {
//            $order['logistics'] = '德邦物流';
//        } elseif ($order['logistics'] == 9) {
//            $order['logistics'] = 'EMS';
//        } elseif ($order['logistics'] == 10) {
//            $order['logistics'] = '其他';
//        }

        if (!empty($order)) {
            $my_auction = Db::table('my_auction')->where('member_id', session('id'))->where('goods_id', $order['g_id'])->find();

            $order['bond_status'] = $my_auction['bond_status'];
            apiResponse(200, '请求成功', array($order));
        } else {
            apiResponse(400, '无记录');
        }
    }

    /**
     * 违约订单 渲染视图
     */
    public function weiyues()
    {
        return view('weiyue');
    }

    /**
     * 我的订单 - 订单详情页 - 已违约
     * /api/orders/weiYue?o_id=7
     */
    public function weiYue(Request $request)
    {
        $param = $request->param();
        $order = Db::table('orders')
            ->field('orders.id,orders.m_id,orders.g_id,s_id,orders.order_sn,orders.shop,orders.goods_name,orders.cover_plan,orders.end_price,orders.postage,orders.status,orders.create_time,bond,end_time as deal_time,orders.name,orders.address,orders.phone,starting_price,close_time')
            ->join('goods', 'orders.g_id = goods.id', 'LEFT')
            ->join('my_auction', 'orders.g_id = my_auction.goods_id', 'LEFT')
            ->where('orders.id', $param['o_id'])
            ->where('orders.m_id', session('id'))
            ->where('orders.status', 5)
            ->find();

        if (!empty($order)) {
            $my_auction = Db::table('my_auction')->where('member_id', session('id'))->where('goods_id', $order['g_id'])->find();

            $order['bond_status'] = $my_auction['bond_status'];
            apiResponse(200, '请求成功', array($order));
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 售后
     */
    public function shouHou()
    {
        $res[] = '售后客服' . ' ' . '1234567890';
        apiResponse(200, '请求成功', $res);
    }




    /**
     *违约处理  计划任务 每N 秒执行一次
     *设置获拍商品规定时间内未付款-修改状态为5-超时(违约)
     * /api/orders/wy
     */
    public function wy()
    {
        //查询所有 订单时间 超时的
        $data = Db::table('orders')
            ->alias('o')
            ->where('status', 1)
            ->where('create_time', '<', time() - 24 * 3600)
            ->select();
        if (!empty($data)) {
            $n = count($data);
            if ($n > 0) {
                foreach ($data as $k => $v) {
                    $this->wychuli($v['id']); //调用处理违约订单方法
                }
            } else {
                return '无违约订单';
            }
        }
    }

    /**
     * 处理超时违约订单
     * /api/orders/wychuli
     */
    public function wychuli($id)
    {
        //根据ID查询单条超时的订单
        $data = Db::table('orders')->where('id', $id)->find();
//        dump($data);
        if ($data['status'] == 1 && $data['create_time'] < time() - 24 * 3600) {
            $update = [
                'status' => 5
            ];
            //修改订单状态 违约
            $res = Db::table('orders')->where('id', $data['id'])->update($update);
            if (!empty($res)) {
                return '已修改违约状态';
            } else {
                return '未修改违约状态';
            }
        }
    }


    /**
     * 设置获拍商品规定时间内未收货-修改状态为4-已收货
     * /api/orders/sh_status?o_id=3
     */
    public function sh_status(Request $request)
    {
        $param = $request->param();

        $data = Db::table('orders')
            ->where('id', $param['o_id'])
            ->find();

        if ($data['status'] == 3) {

            Db::table('orders')
                ->where('id', $param['o_id'])
                ->update(['status' => 4]);

            apiResponse(200, '收货成功');
        } else {
            apiResponse(400, '收货失败');
        }

    }

    /**
     * 设置获拍商品规定的时间内未收货-修改状态为 4 已收货  利用计划 任务
     * /api/orders/sh
     */
    public function sh()
    {
        $data = Db::table('orders')->alias('o')
            ->where('status', 3)
            ->whereTime('logistics_time', '<', strtotime('-10 day', time()))//发货时间 大于 当前时间 减10天
            ->select();
        if (!empty($data)) {
            $n = count($data);

            if ($n > 0) {
                foreach ($data as $k => $v) {
//                    dump($v);
                    $this->shchuli($v['id']); //调用确认收货状态修改方法
                }

            } else {
                return '没有超时未确认收货订单';
            }
        }

    }

    /**
     * 处理超时没有确定收货的订单
     *
     */
    public function shchuli($id)
    {
        $data = Db::table('orders')->where('id', $id)->find();
        if ($data['status'] == 3) {
            $update = [
                'status' => 4
            ];
            $res = Db::table('orders')->where('id', $data['id'])->update($update);
            if (!empty($res)) {
                return '已修改为确认收货';
            } else {
                return '未修改';
            }
        }

    }
}
