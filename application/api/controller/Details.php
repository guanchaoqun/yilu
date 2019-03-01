<?php

namespace app\api\controller;

use app\common\model\Cates;
use app\common\model\Goods;
use think\Controller;
use think\Paginator;
use think\Request;
use think\Db;


class Details extends Base
{
    public function vie()
    {
        $wxdata = getWxConfig();
        return view('ongoing', ['wxdata' => $wxdata]);
    }

    /**
     * @function 商品详情页接口
     * @param  id
     * @return json
     * @API   /api/details/index/id/1
     */
    public function index($id = 0)
    {
        if (empty($id)) {
            apiResponse('420', '缺少参数 ');
        }
        if (!empty($id)) {

            //当用户访问商品时增加访问 量
            $shopid = Db::table('goods')
                ->field('shop.id')
                ->where('goods.id', $id)
                ->join('artist', 'artist.id=goods.artist_id', 'LEFT')
                ->join('shop', 'shop.art_id=artist.id', 'LEFT')
                ->find();
            if (empty($shopid)) {
                apiResponse('400', '未找到');
            }

            $data = [
                'shop_id' => $shopid['id'],
                'create_time' => time(),
                'views' => 1
            ];
            $views = Db::table('views')->where('create_time', '>', strtotime('-1 day', time()))->where('shop_id', $shopid['id'])->find();
            //向访问量记录表中插入一条记录 如果创建时间大于 当前的时间减一天的时间 ,访问一次此控制器方法字段自加 1
            //如果创建时间  小于 当前时间减一天时间 ,新加一条数据 ,自增新加记录的访问量值
            if ($views['create_time'] > strtotime('-1 day', time())) {
                $res = Db::table('views')
                    ->where('shop_id', $shopid['id'])
                    ->whereTime('create_time', '>', strtotime('-1 day', time()))
                    ->setInc('views');
            } else {
                $res = Db::table('views')->insert($data);
            }


            //商品的详情数据
            $field = [
                'goods.id','goods.cates_id','cname', 'shop.shopname', 'shop.id as sp_id', 'shop.shopimage',
                'artist_class.name as classname', 'artist.name as artisname',
                'artist_id', 'top', 'goods.goods_name', 'volume', 'goods.bond', 'starting_price',
                'range_price', 'postage', 'start_time', 'end_time', 'size', 'auctionstatus',
                'decoration', 'goods.image', 'particulars', 'cover_plan', 'decade', 'author'
            ];
            $details = model('goods')->where('goods.id', $id);
            $details->field($field);
            $details->join('artist', 'goods.artist_id=artist.id', 'LEFT');
            $details->join('artist_class', 'artist_class_id=artist_class.id', 'LEFT');
            $details->join('shop', 'artist.id=shop.art_id', 'LEFT');
            $details->join('cates','cates.id=goods.cates_id');
//            $details->join('bond_orders','bond_orders.g_id=goods.id','LEFT');
            $data = $details->select();

            $price = Db::table('goods_offer')->where('goods_id', $id)->max('price');
            if (!empty($data)) {
                foreach ($data as $k => $v) {
//                    if (time() >= strtotime($v['start_time']) && time() < strtotime($v['end_time'])) {
                    if ($v['auctionstatus'] == 1) {

                        //正在进行拍卖
                        $v['surplustime'] = strtotime($v['end_time']) - time();
                        $v['goodsstart'] = '正在进行';
                        $v['maxprice'] = $price;
//                        $v['maxprice'] = number_format($price,2);
                        $bondstatus = Db::table('bond_orders')->alias('b')->field('b.status')
                            ->where('m_id', session('id'))
                            ->where('b.g_id', $v['id'])->value('status');
                        if (!empty($bondstatus)) {
                            $v['bondstatus'] = $bondstatus;
                        } else {
                            $v['bondstatus'] = 0;
                        }
                        $dangqianjia = Db::table('goods_offer')->alias('d')->where('goods_id', $v['id'])->max('price');
                        if (!empty($dangqianjia)) {
                            $v['dangqianjia'] = number_format($dangqianjia, 2);
                            $v['chujia'] = $dangqianjia;
                        } else {
                            $v['dangqianjia'] = $v['starting_price'];
                            $v['chujia'] = $v['starting_price'];
                        }
//                        if ($v['auctionstatus'] != 1) {
//                            Db::table('goods')->where('id', $v['id'])->update(['auctionstatus' => 1]);
//                            $details = model('goods')->where('goods.id', $id);
//                            $details->field($field);
//                            $details->join('artist', 'goods.artist_id=artist.id', 'LEFT');
//                            $details->join('artist_class', 'artist_class_id=artist_class.id', 'LEFT');
//                            $details->join('shop', 'artist.id=shop.art_id', 'LEFT');
//                            $details->join('bond_orders','bond_orders.g_id=goods.id','LEFT');
//
//                            $data = $details->select();
//
//                        }
//                    } elseif (time() < strtotime($v['start_time'])) {
                    } elseif ($v['auctionstatus'] == 0) {
                        //距离开始的时间
                        $v['preparetime'] = strtotime($v['start_time']) - time();
                        $v['goodsstart'] = '即将开始';
                        $v['maxprice'] = $v['starting_price'];
//                        $v['maxprice'] =$price;
//                        $v['maxprice'] = number_format($price,2);
                        $bondstatus = Db::table('bond_orders')->alias('b')->field('b.status')
                            ->where('m_id', session('id'))
                            ->where('b.g_id', $v['id'])
                            ->value('status');
                        if (!empty($bondstatus)) {
                            $v['bondstatus'] = $bondstatus;

                        } else {
                            $v['bondstatus'] = 0;
                        }
                        $dangqianjia = Db::table('goods_offer')->alias('d')->where('goods_id', $v['id'])->max('price');
                        if (!empty($dangqianjia)) {
                            $v['dangqianjia'] = number_format($dangqianjia, 2);
                            $v['chujia'] = $dangqianjia;
                        } else {
                            $v['dangqianjia'] = $v['starting_price'];
                            $v['chujia'] = $v['starting_price'];
                        }
//                        if ($v['auctionstatus'] != 0) {
//                            DB::table('goods')->where('id', $v['id'])->update(['auctionstatus' => 0]);
//                            $details = model('goods')->where('goods.id', $id);
//                            $details->field($field);
//                            $details->join('artist', 'goods.artist_id=artist.id', 'LEFT');
//                            $details->join('artist_class', 'artist_class_id=artist_class.id', 'LEFT');
//                            $details->join('shop', 'artist.id=shop.art_id', 'LEFT');
//
//                            $data = $details->select();
//
//                        }
//                    } elseif (time() > strtotime($v['end_time'])) {
                    } elseif ($v['auctionstatus'] == 2) {

                        $v['goodsstart'] = '结束拍卖';
                        $v['maxprice'] = $price;
//                        $v['maxprice'] = number_format($price,2);
                        $bondstatus = Db::table('bond_orders')->alias('b')->field('b.status')
                            ->where('m_id', session('id'))
                            ->where('b.g_id', $v['id'])
                            ->value('status');
//                        dump($bondstatus);
                        if (!empty($bondstatus)) {
                            $v['bondstatus'] = $bondstatus;
                        } else {
                            $v['bondstatus'] = 0;
                        }
                        $dangqianjia = Db::table('goods_offer')->alias('d')->where('goods_id', $v['id'])->max('price');
                        if (!empty($dangqianjia)) {
                            $v['dangqianjia'] = number_format($dangqianjia, 2);
                            $v['chujia'] = $dangqianjia;
                        } else {
                            $v['dangqianjia'] = $v['starting_price'];
                            $v['chujia'] = $v['starting_price'];
                        }
//                        if ($v['auctionstatus'] != 2) {
////                            DB::table('goods')->where('id', $v['id'])->update(['auctionstatus' => 2]);
//
//                            $details = model('goods')->where('goods.id', $id);
//                            $details->field($field);
//                            $details->join('artist', 'goods.artist_id=artist.id', 'LEFT');
//                            $details->join('artist_class', 'artist_class_id=artist_class.id', 'LEFT');
//                            $details->join('shop', 'artist.id=shop.art_id', 'LEFT');
//
//                            $data = $details->select();
//
//                        }
                    } elseif ($v['auctionstatus'] == 3) {

                        $v['goodsstart'] = '拍卖成交';
                        $v['maxprice'] = $price;
//                        $v['maxprice'] = number_format($price,2);
                        $bondstatus = Db::table('bond_orders')->alias('b')->field('b.status')
                            ->where('m_id', session('id'))
                            ->where('b.g_id', $v['id'])
                            ->value('status');
                        if (!empty($bondstatus)) {
                            $v['bondstatus'] = $bondstatus;
                        } else {
                            $v['bondstatus'] = 0;
                        }
                        $dangqianjia = Db::table('goods_offer')->alias('d')->where('goods_id', $v['id'])->max('price');
                        if (!empty($dangqianjia)) {
                            $v['dangqianjia'] = number_format($dangqianjia, 2);
                            $v['chujia'] = $dangqianjia;
                        } else {
                            $v['dangqianjia'] = $v['starting_price'];
                            $v['chujia'] = $v['starting_price'];
                        }

                    } elseif ($v['auctionstatus'] == 9) {
                        $v['goodsstart'] = '流拍';
                        $v['maxprice'] = $price;
                        $bondstatus = Db::table('bond_orders')->alias('b')->field('b.status')
                            ->where('m_id', session('id'))
                            ->where('b.g_id', $v['id'])
                            ->value('status');
                        if (!empty($bondstatus)) {
                            $v['bondstatus'] = $bondstatus;
                        } else {
                            $v['bondstatus'] = 0;
                        }
                        $dangqianjia = Db::table('goods_offer')->alias('d')->where('goods_id', $v['id'])->max('price');
                        if (!empty($dangqianjia)) {
                            $v['dangqianjia'] = number_format($dangqianjia, 2);
                            $v['chujia'] = $dangqianjia;
                        } else {
                            $v['dangqianjia'] = $v['starting_price'];
                            $v['chujia'] = $v['starting_price'];
                        }

                    }
                }
                apiResponse(200, '获取成功', $data);
            } else {
                apiResponse(400, '获取失败');
            }
        }


    }

    /**
     * 出价记录渲染视图
     */
    public function record()
    {
        return view('record');
    }

    /**
     * 获取拍品出价记录
     *
     *auction.jiangliping.com/api/details/offer?g_id=&page=
     */
    public function offer()
    {
        //获取商品ID
        $g_id = input('get.g_id');

        //获取分页参数
//        if (!empty(input('get.page'))) {
//            $page = input('get.page');
//        } else {
//            $page = 1;
//        }

        //获取数据 与商品表-用户表联查
        $res = Db::table('goods_offer')
            ->field('goods_offer.id,goods_offer.member_id,goods_offer.goods_id,goods_offer.create_time,goods_offer.price,nickname,goods_name,goods_offer.offerstatus')
            ->join('member', 'goods_offer.member_id=member.id', 'LEFT')
            ->join('goods', 'goods_offer.goods_id=goods.id', 'LEFT')
            ->where('goods_id', $g_id)
//            ->page($page, 10)
            ->order('price desc,create_time desc')
            ->select();
//        dump($res);

        if (!empty($res)) {
            foreach ($res as $k => $v) {
                $time = date('m-d H:i:s', $v['create_time']);
                $v['create_time'] = $time;

                $arr[] = $v;
            }


            apiResponse(200, '请求成功', $arr);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 店铺页面渲染视图
     *
     */
    public function focusStore()
    {
        return view('focusStore');
    }

    /**
     * @param int $id
     * @function  商品店铺页面
     * @ http://auction.jiangliping.com/api/details/shop/id/1
     * @throws
     */
    public function shop($id = 0)
    {
        if (!empty($id)) {
            $field = [
                'shop.id as shop_id', 'shopname', 'shop.art_id',
                'artist_class.name as classname', 'artist.name',
                'goods.id as gs_id', 'goods.start_time', 'goods.end_time'
                , 'goods_name', 'goods.image', 'shopimage', 'author', 'cover_plan'
                , 'starting_price'
            ];
            $shopdata = Db::table('shop')
                ->field($field)->where('shop.id', $id)
                ->join('artist', 'shop.art_id=artist.id', 'LEFT')
                ->join('artist_class', 'artist_class_id=artist_class.id', 'LEFT')
                ->join('goods', 'goods.artist_id=artist.id', 'LEFT')
                ->where('goods.status', 1)
                ->whereTime('goods.end_time', '>', date('Y-m-d H:i:s', time()))
                ->select();

            if (!empty($shopdata)) {
                foreach ($shopdata as $k => $v) {
                    //获取某一个商品的最高出价 加入到 数组中
                    $v['price'] = Db::table('goods_offer')
                        ->where('goods_id', $v['gs_id'])
                        ->max('price');
                    //加入剩余时间戳
                    if (time() >= strtotime($v['start_time']) && time() < strtotime($v['end_time'])) {
                        $v['surplustime'] = strtotime($v['end_time']) - time();
                        $shopdata[$k]['surplustime'] = $v['surplustime'];
                    }
                    //加加入即将开始 时间戳
                    if (time() < strtotime($v['start_time'])) {
                        $v['preparetime'] = strtotime($v['start_time']) - time();
                        $shopdata[$k]['preparetime'] = $v['preparetime'];
                    }
                    //最高出价
                    $shopdata[$k]['maxprice'] = $v['price'];
                }
                apiResponse(200, '请求成功', $shopdata);
            }
            apiResponse(400, '请求失败');

        }


    }

    /**
     * 店铺页面头部
     * /api/details/shophead?id=
     */

    public function shophead($id = 0)
    {
        if (!empty($id)) {
            $data = Db::table('shop')
                ->alias('s')
                ->field('shopname,artist_class.name,shopimage')
                ->join('artist', 'artist.id=shop.art_id', 'LEFT')
                ->join('artist_class', 'artist_class.id=artist_class_id', 'LEFT')
                ->where('s.id', $id)->find();

            $fansum = Db::table('follow')->where('shop_id', $id)->where('followstatus', 1)->count();


            $ordersum = Db::table('orders')->where('s_id', $id)->where('status', 4)->count();
            //粉丝 数量
            $data['fansum'] = $fansum;
            //此店铺 总成交的数量
            $data['ordersum'] = $ordersum;

            $follow = Db::table('follow')->where('shop_id', $id)->where('member_id', session('id'))->value('followstatus');
            if (!empty($follow)) {
                $data['followstatus'] = $follow;
            } else {
                $data['followstatus'] = 0;
            }

//           halt($follow);
            $res = [];
            foreach ($data as $k => $v) {
                $res[$k] = $v;
            }
            //处理数组格式
            $a[] = $res;

            apiResponse('200', '获取成功', $a);
        }
        apiResponse('400', '获取失败');

    }

    /**
     * 保证金页面渲染视图
     */
    public function sign()
    {
        return view('delivery');
    }

    /**
     *详情页点击报名按键 进入报名页面
     *    参数  商品ID
     *   /api/details/signup
     */
    public function signup($id)
    {
        if (!empty($id)) {

            $bond = Db::table('goods')->field('id,bond')->where('id', $id)->find();
            $add = Db::table('address')->field('id as aid,area,address,name,phone')->where('state', 1)->where('m_id', session('id'))->find();
            $phone = Db::table('member')->where('id', session('id'))->find();

            if (empty($add)) {
                $data['aid'] = '';
                $data['area'] = '';
                $data['address'] = '';
                $data['name'] = '';

                apiResponse('400', '没有地址请添加');
            }
            if (empty($bond)) {
                $data['bond'] = $bond['bond'];
                $data['id'] = $bond['id'];
                $data['bond'] = '';
                apiResponse('460', '商品无保证金');
            }
            if (empty($phone['phone'])) {

                apiResponse('450', '请绑定手机号');
            }
            $data['id'] = $bond['id'];
            $data['aid'] = $add['aid'];
            $data['area'] = $add['area'];
            $data['address'] = $add['address'];
            $data['name'] = $add['name'];
            $data['phone'] = $add['phone'];
            $data['bond'] = $bond['bond'];

            apiResponse(200, '获取成功', array($data));
        }
        apiResponse(400, '获取失败');
    }


    /**
     * 点击交保证 金报名  保存一条数据到 my_auction 表中状态 为 0
     *  /api/details/my_auction
     */
    public function myauction()
    {
//            session('id',11);//临时
//        if (empty(session('id'))) {
//            $this->redirect(url('/api/wechat/snsapiUserinfo'));
//        }
        $phone = Db::table('member')->where('id', session('id'))->value('phone');
//        //判断用户是否绑定手机 如果没有,先绑定手机后再报名
        if (empty($phone)) {
            apiResponse('400', '绑定手机后报名');
        }

        $g_id = request()->param('id');

        if (!empty($g_id)) {
            $res1 = Db::table('my_auction')->field('member_id,goods_id,bond_status')->where('goods_id', $g_id)->where('member_id', session('id'))->find();
//            $res0 = Db::table('bond_orders')->where('g_id',$g_id)
//                        ->where('m_id',session('id'))
//                        ->find();

            $add = Db::table('address')->field('id,address')->where('m_id', session('id'))->find();
            //如果用户没有地址  先填写地址
            if (empty($add)) {
                apiResponse(202, '请先添加地址');
            }
            //如果没有报名先报名  生成保证金订单
            if (empty($res1)) {
                $data = [
                    'goods_id' => $g_id,
                    'member_id' => session('id'),
                    'create_time' => time(),
                    'jieshumoban'=>1,
                    'kaishimoban'=>1

                ];
                $res = Db::table('my_auction')->insert($data);

                $g_data = Db::table('goods')->field('bond')->where('id', $g_id)->find();
                if (!empty($res)) {
                    //报名成功 生成订单
                    $insert['goods_name'] = '保证金';
                    $insert['order_sn'] = 'BZJ' . get_vc(4, 2) . time();
                    $insert['m_id'] = \session('id');
                    $insert['create_time'] = time();
                    $insert['status'] = 0;
                    $insert['g_id'] = $g_id;
                    $insert['bond'] = $g_data['bond'];
                    //生成订单 插入数据库

                    $res2 = Db::table('bond_orders')->where('m_id', session('id'))
                        ->where('g_id', $g_id)->find();

                    if (empty($res2)) {
                        $id = db('bond_orders')->insertGetId($insert);

                    }
                    $data1 = [
                        'id' => $id
                    ];
                    //如果 报名成功 了 前台再次点击 按键时 调 起支付页面
                    apiResponse(201, '报名成功点击支付保证金', $data1);
                }
            }
        }
        if (!empty($res1)) {
            $data2 = Db::table('bond_orders')->where('m_id', session('id'))->where('g_id', $g_id)->find();

            apiResponse(202, '已经报过名了', $data2);
        }
    }
}