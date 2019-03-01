<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Request;


class Orderscorntab extends Controller
{

    /**
     * 等待开始的商品 状态 为 0
     * /api/orders/ddks
     */
    public function ddks()
    {

        $up = ['auctionstatus' => 0];

        Db::table('goods')
            ->where('auctionstatus','<>',0)
            ->whereTime('start_time', '>', date('Y-m-d H:i:s', time()))
            ->update($up);
        //查询即将开始的拍品修改状态
//        $readygoods = Db::table('goods')
//            ->field('id,goods_name,auctionstatus')
//            ->whereTime('start_time', '>', date('Y-m-d H:i:s', time()))
//            ->select();

        }


    /**
     * 处理即将开始的拍品  计划任务  开始拍品修改状态
     * /api/orders/kaishi
     */
    public function kaishi()
    {
        $update = ['auctionstatus'=>1];
        Db::table('goods')->where('auctionstatus',0)
            ->whereTime('start_time','<',date('Y-m-d H:i:s',time()))
            ->whereTime('end_time','>',date('Y-m-d H:i:s',time()))
            ->update($update);
    }

    /**
     * 处理已经到时的拍品 计划任务 结束拍品修改状态
     *
     *  /api/orders/jieshu
     */
    public function jieshu()
    {
        $update = ['auctionstatus' => 2];
        Db::table('goods')
            ->where('auctionstatus','1')
            ->whereTime('end_time','<', date('Y-m-d H:i:s', time()))
            ->update($update);
        //查询 已经结束的商品
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
            $id = Db::table('orders')
                ->insertGetId($arr);

            if (!empty($id)) {
                    $this->sendTransaction($id);
                apiResponse(200, '添加订单成功');
            } else {
                apiResponse(400, '添加订单失败');
            }
        } else {
            apiResponse(400, '订单已存在 查看订单');
        }
    }


    /**
     *违约处理  计划任务 每N 秒执行一次
     *设置获拍商品规定时间内未付款-修改状态为5-超时(违约)
     * /api/orders/wy
     */
    public function wy()
    {
        //查询所有 订单时间 超时的
        $update = [
            'status' => 5,
            'close_time'=>date('Y-m-d H:i:s')
        ];

        Db::table('orders')
            ->where('status',1)
            ->whereTime('create_time','<',time()-24*3600)
            ->update($update);

    }

    /**
     * 设置获拍商品规定的时间内未收货-修改状态为 4 已收货  利用计划 任务
     * /api/orders/sh
     */
    public function sh()
    {
        $update=['status'=>4];


        Db::table('orders')
            ->where('status',3)
            ->whereTime('logistics_time', '<', strtotime('-10 day', time()))
            ->update($update);


    }



    /**
     * 即将结束拍卖 发送模版消息 提醒
     * /api/orderscorntab/jjjieshu
     */
    public function jjjieshu()
    {
        $data=Db::table('goods')->field('id,end_time')->where('status',1)->where('auctionstatus',1)->select();
//            dump($data);
        if(!empty($data)){
            foreach($data as $k => $v){
//                dump($v);
                if(strtotime($v['end_time'])-600>time()&& strtotime($v['end_time'])-700<time()){
                    $auction=Db::table('my_auction')->where('jieshumoban',1)->where('goods_id',$v['id'])->select();
                    if(!empty($auction)){
                        foreach($auction as $kk => $vv){
                            $this->members($vv);
                        }
                    }
                }
            }
        }else{
            return '无';
        }
    }
    /**
     * 报名的用户
     */
    public function members($vv)
    {
//        dump($vv);
        $res1=Db::table('goods_offer')->where('goods_id',$vv['goods_id'])->max('price');

        $res=Db::table('goods')->field('goods.id,goods_name')->where('id',$vv['goods_id'])->find();

       $data= Db::table('member')->field('id,openid')->where('id',$vv['member_id'])->find();
       $data['goods_name']=$res['goods_name'];
       $data['price']=$res1;
       $data['goods_id']=$res['id'];
//        dump($data);
           if(!empty($data)){
            $this->sendjieshu($data);
        }
    }

    /**
     * 发送提醒
     */
    public function sendjieshu($res)
    {
//dump($res);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . access_token();

//    接受模板消息的用户openid
        $openid = $res['openid'];
//        模板消息id
        $template_id = '8bSQswuf_mLEeb5mmdtpnFKjQILUvlZgo7BoEzKpp-Y';
//        设置模板消息
        $array = array();
//        设置接受消息用户的openid
        $array['touser'] = $openid;
//        设置模板消息id
        $array['template_id'] = $template_id;
        $array['url'] = 'http://www.yiluzongheng.com/home/ongoing?id='.$res['goods_id'].'&g_id='.$res['goods_id'];
//        设置模板消息
        $data = array();
        $data['first'] = array();
        $data['first']['value'] = '您好！您参与的拍卖即将结束';
        $data['first']['color'] = '#173177';
        $data['keyword1'] = array();
        $data['keyword1']['value'] = $res['goods_name'];
        $data['keyword1']['color'] = '#173177';
        $data['keyword2'] = array();
        $data['keyword2']['value'] = $res['price'];
        $data['keyword2']['color'] = '#173177';
        $data['remark'] = array();
        $data['remark']['value'] = '您参与的拍卖距离结束仅剩10分钟';
        $data['remark']['color'] = '#173177';
        $array['data'] = $data;
//        dump($data);
//        调用公共方法curl_post，发送模板消息
        $data1=curl_post($array, $url);
//        dump($data1);
        if(!empty($data1)){
            $update=['jieshumoban'=>2];
            Db::table('my_auction')->where('goods_id',$res['goods_id'])->where('member_id',$res['id'])->update($update);
        }
    }

    /**
     * 即将开始  发送模版消息提醒
     * /api/orderscorntab/sendjieshu
     *
     */
    public function jjkaishi()
    {
       $data= Db::table('goods')->where('status',1)->where('auctionstatus',0)->select();
        if(!empty($data)){
            foreach($data as $k => $v){
                if(strtotime($v['start_time'])-540>time()&& strtotime($v['start_time'])-600<time()){
                    $auction=Db::table('my_auction')->where('kaishimoban',1)->where('goods_id',$v['id'])->select();
                    if(!empty($auction)){
                        foreach($auction as $kk => $vv){
                            $this->auctions($vv);
                        }
                    }
                }
            }
        }else{
            return '没有';
        }
    }

    /**
     *
     */
    public function auctions($vv)
    {

    }
    /**
     * @param
     * 发送模版消息
     */
    public function sendkaishi($res)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . access_token();

//    接受模板消息的用户openid
        $openid = $res['openid'];
//        模板消息id
        $template_id = '8bSQswuf_mLEeb5mmdtpnFKjQILUvlZgo7BoEzKpp-Y';
//        设置模板消息
        $array = array();
//        设置接受消息用户的openid
        $array['touser'] = $openid;
//        设置模板消息id
        $array['template_id'] = $template_id;
        $array['url'] = 'http://www.yiluzongheng.com/home/my';
//        设置模板消息
        $data = array();
        $data['first'] = array();
        $data['first']['value'] = '您好！您参与的拍卖即将结束';
        $data['first']['color'] = '#173177';
        $data['keyword1'] = array();
        $data['keyword1']['value'] = $res['goods_name'];
        $data['keyword1']['color'] = '#173177';
        $data['keyword2'] = array();
        $data['keyword2']['value'] = $res['price'];
        $data['keyword2']['color'] = '#173177';
        $data['remark'] = array();
        $data['remark']['value'] = '您参与的拍卖距离结束仅剩10分钟';
        $data['remark']['color'] = '#173177';
        $array['data'] = $data;
//        调用公共方法curl_post，发送模板消息
        $res=curl_post($array, $url);
//        if(!empty($res)){
////            $update=['kaishimoban'=>2 ];
////            Db::table('my_auction')->where('member_id',$res['id'])-update($update);
//        }
    }

    /**
     * 订单生成  成交提醒
     * /api/orderscorntab/sendTransaction
     */
    public function sendTransaction($id)
    {
        if(empty($id)){
            die();
        }
            $res=Db::table('orders')
                ->field('orders.id,orders.goods_name,orders.end_price,orders.create_time,member.openid')
                ->where('orders.id',$id)
//                ->field('id')
                ->join('member','member.id = m_id')
                ->find();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . access_token();

//    接受模板消息的用户openid
        $openid = $res['openid'];
        //模板消息id
        $template_id = 'dk0wFYvVxIdhYKKlMOV7Ia6bEmFBNDiV92HOPg4K7nQ';
        //设置模板消息
        $array = array();
        //设置接受消息用户的openid
        $array['touser'] = $openid;
        //设置模板消息id
        $array['template_id'] = $template_id;
        $array['url'] = 'http://www.yiluzongheng.com/api/goodspay/index?id='.$id;
        //设置模板消息    $data = array();
        $data['first'] = array();
        $data['first']['value'] = '你好，您的拍卖商品已中拍';
        $data['first']['color'] = '#173177';
        $data['keyword1'] = array();
        $data['keyword1']['value'] = $res['goods_name'];
        $data['keyword1']['color'] = '#173177';
        $data['keyword2'] = array();
        $data['keyword2']['value'] = $res['end_price'];
        $data['keyword2']['color'] = '#173177';
        $data['keyword3'] = array();
        $data['keyword3']['value'] = date('Y-m-d H:i:s',$res['create_time']);
        $data['keyword3']['color'] = '#173177';
        $data['remark'] = array();
        $data['remark']['value'] = '您好，您的拍卖商品已中拍，请您一天内及时付款，点击消息进行进行付款。';
        $data['remark']['color'] = '#173177';
        $array['data'] = $data;
        //调用公共方法curl_post，发送模板消息
        curl_post($array, $url);

    }

/**
 * 定时 任务
 * 发货提醒
 * api/orderscorntab/fahuomoban
 */
    public function fahuomoban()
    {
       $data=Db::table('orders')->where('status',3)->where('fahuo',1)->select();
       if($data){

           foreach($data as  $k =>$v){
               $this->sendTemplateMessage($v);
           }
       }
    }
    /**
     * 发送发货提醒 模版消息
     *   /api/weixin/sendTemplateMessage
     */

   public function sendTemplateMessage($v)
    {
       $res= Db::table('member')->where('id',$v['m_id'])->find();
//        $res = $request->param();
//        $id = $res['id'];
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . access_token();

//    接受模板消息的用户openid
        $openid = $res['openid'];
//        $openid = 'oMXiawqeKDHfc4xX1ynOymSmlo9c';
        //模板消息id
        //发货通知
        $template_id = 'QiqHMKWE7PoHhknLbic84cGn8g0fmAJz-7ITkuPqf7E';
        //设置模板消息
        $array = array();
        //设置接受消息用户的openid
        $array['touser'] = $openid;
        //设置模板消息id
        $array['template_id'] = $template_id;
        $array['url'] = 'http://www.yiluzongheng.com/home/daishouhuo?o_id='.$v['id'];
        //设置模板消息    $data = array();
        $data['first'] = array();
        $data['first']['value'] = '您好,您的订单已发货';
        $data['first']['color'] = '#173177';
        $data['keyword1'] = array();
        $data['keyword1']['value'] = $v['logistics'];
        $data['keyword1']['color'] = '#173177';
        $data['keyword2'] = array();
        $data['keyword2']['value'] = $v['logistics_number'];
        $data['keyword2']['color'] = '#173177';
        $data['keyword3'] = array();
        $data['keyword3']['value'] = $v['goods_name'];
        $data['keyword3']['color'] = '#173177';
        $data['keyword4'] = array();
        $data['keyword4']['value'] = '1';
        $data['keyword4']['color'] = '#173177';
        $data['remark'] = array();
        $data['remark']['value'] = '请注意查收';
        $data['remark']['color'] = '#173177';
        $array['data'] = $data;
        //调用公共方法curl_post，发送模板消息
       $res= curl_post($array, $url);

        if (!empty($res)){
            $update=['fahuo'=>2];
            Db::table('orders')->where('fahuo',1)->where('id',$v['id'])->update($update);
        }
    }


}