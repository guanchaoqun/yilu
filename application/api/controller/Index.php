<?php

namespace app\api\controller;

use app\common\model\Cates;
use app\common\model\Goods;
use think\Controller;
use think\Paginator;
use think\Request;
use think\Db;


class Index extends Base
{

    public function index()
    {
        $wxdata = getWxConfig();
        return view('index', ['wxdata' => $wxdata]);
    }

    /**
     * @function 拍卖首页接口
     * @param null/page
     * @return json
     */


    /**
     * 首页轮播图
     * @return  id  banner_img  banner_url
     *  * http://auction.jiangliping.com/api/index/banner
     */
    public function banner()
    {

        $res = Db::table('banner')->where('status', 1)->limit(3)->select();

        foreach ($res as $k => $v) {
            $start_time = strtotime($v['start_time']);
            $end_time = strtotime($v['end_time']);

            if (time() < $end_time && time() > $start_time) {
                $arr[] = $v;
            }
        }
        if (!empty($arr)) {
            apiResponse(200, '请求成功', $arr);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * @function 商品分类
     * @param   id cname
     * @return
     * http://auction.jiangliping.com/api/index/cates
     */
    public function cates()
    {
//        $res = Db::table('cates')->where('status', 1)->column('id,cname');
        $res = Db::table('cates')->where('status', 1)->select();
        //压入数组
        $cates = $res;

        if ($cates) {
            apiResponse(200, '请求成功', $cates);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * @function 商品列表
     * @参数  分页 page  默认 1  每页显示 6
     *       分类 cates 默认为空
     *       price 根据起拍价格排序 1由高到低  2 由低到高      默认为空
     *       source 根据商品来源分类   1 艺术家  2  收藏家    默认为空
     *       readystatus 根据时间筛选 1 即将开始 2  正在进行  默认为空
     * @return code 200  400
     *        message
     *        data
     * /api/index/goodslist/?page=1&cates=&price=1&source=1&readytime=1
     */
    public function goodslist(Request $request)
    {
        //获取分页参数

        $param = $request->param();
        if (!empty($param['page'])) {$page = $param['page'];} else {$page = 1;}

        if (!empty($param['cates'])) {$cates = $param['cates'];} else {
            //如果没有传分类 参数,生成参数为1-100;
            $cates = range(1, 20);
        }

        if (!empty($param['source'])) {$source = $param['source'];} else {$source = '';}
        //获取数据
        $field = ['goods.id', 'cates_id', 'artist_class.name as classname', 'artist.name as artisname', 'artist_id', 'top', 'goods_name', 'volume', 'bond', 'starting_price', 'range_price', 'postage', 'start_time', 'end_time', 'goods.image', 'cover_plan', 'auctionstatus'];

        $goodsModel = model('goods')->field($field);
        $goodsModel->join('artist', 'goods.artist_id=artist.id', 'LEFT');
        $goodsModel->join('artist_class', 'artist_class_id=artist_class.id', 'LEFT');
        $goodsModel->where('goods.status', 1)->whereTime('end_time', '>', date('Y-m-d H:i:s', time()));
        $goodsModel->whereIn('cates_id', $cates);
        if (!empty($param['readytime']) && $param['readytime'] == 1) {//参数为2时筛选 即将开始的拍品
            $goodsModel->whereTime('start_time', '>', date('Y-m-d H:i:s', time()));
        };
        if (!empty($param['readytime']) && $param['readytime'] == 2) {
            $goodsModel->whereTime('start_time', '<', date('Y-m-d H:i:s', time()));
            $goodsModel->whereTime('end_time', '>', date('Y-m-d H:i:s', time()));
        };
        if ($source == 1) {
            $artistid = Db::table('artist')->field('id')->where('artist_class_id', $source)->select();

            $result = array_column($artistid, 'id');
            $goodsModel->whereIn('artist_id', $result);
        } elseif ($source == 2) {
            $artistid = Db::table('artist')->field('id')->where('artist_class_id', $source)->select();
            $result = array_column($artistid, 'id');
            $goodsModel->whereIn('artist_id', $result);
        };
        if (!empty($param['price']) && $param['price'] == 1) {
            $goodsModel->order("top desc,starting_price desc,end_time asc");
        } elseif (!empty($param['price']) && $param['price'] == 2) {
            $goodsModel->order("top desc,starting_price asc,end_time asc");
        } else {
            $goodsModel->order("top desc,end_time asc");
        }
        $goodsModel->page($page, 16);

        $data = $goodsModel->select();

        if (!empty($data)) {
            foreach ($data as $k => $v) {
                //正在进行
                if ($v['auctionstatus'] == 1) {
                    $v['surplustime'] = strtotime($v['end_time']) - time();
                    $v['starts_time'] = strtotime($v['start_time']);
                    $v['ends_time'] = strtotime($v['end_time']);
                    $v['image'] = explode(',', $v['image']);
                    $price = Db::table('goods_offer')->where('goods_id', $v['id'])->max('price');
                    if ($price != 0) {
                        $v['maxprice'] = number_format($price, 2);
                    } else {
                        $v['maxprice'] = $v['starting_price'];
                    }
                }
                //还没开始
                if ($v['auctionstatus'] == 0) {
                    $v['preparetime'] = strtotime($v['start_time']) - time();
                    $v['starts_time'] = strtotime($v['start_time']);
                    $v['ends_time'] = strtotime($v['end_time']);
                    $v['image'] = explode(',', $v['image']);
                    $v['maxprice'] = $v['starting_price'];
                }
                //已经结束
                if ($v['auctionstatus'] == 2) {
                    $v['goodsstart'] = '结束拍卖';
                    $v['starts_time'] = strtotime($v['start_time']);
                    $v['ends_time'] = strtotime($v['end_time']);
                    $v['image'] = explode(',', $v['image']);

                    $price1 = Db::table('goods_offer')->where('goods_id', $v['id'])->max('price');
                    if ($price1!= 0) {
                        $v['maxprice'] = number_format($price1, 2);
                    } else {
                        $v['maxprice'] = $v['starting_price'];
                    }
                }
            }
            apiResponse(200, '获取成功', $data);
        }
        apiResponse(400, '获取失败');
    }
}

