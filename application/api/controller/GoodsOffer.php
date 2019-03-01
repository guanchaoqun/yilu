<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class GoodsOffer extends Controller
{

    /**
     * 获取拍品出价
     *auction.jiangliping.com/api/goods_offer/index?g_id=&page=
     */
    public function index()
    {
        //获取商品ID
        $g_id = input('get.g_id');
        //获取分页参数
//        if(!empty(input('get.page'))){
//            $page = input('get.page');
//        }else{
//            $page = 1;
//        }
        //获取数据 与商品表-用户表联查
        $res = Db::table('goods_offer')
            ->field('goods_offer.id,goods_offer.member_id,goods_offer.goods_id,goods_offer.create_time,goods_offer.price,nickname,goods_name')
            ->join('member','goods_offer.member_id=member.id','LEFT')
            ->join('goods','goods_offer.goods_id=goods.id','LEFT')
            ->where('goods_id', $g_id)
//            ->page($page,10)
            ->order('price desc')
            ->select();
        if (!empty($res)) {
            foreach ($res as $k => $v) {
                $time = date('m-d H:i:s',$v['create_time']);
                $v['create_time'] = $time;
                $arr[]=$v;
            }
            apiResponse(200,'请求成功',$arr);
        }else{
            apiResponse(400,'请求失败');
        }
    }

        /**
         * 商品出价
         * /api/goods_offer/buy?id=1
         */
        public function  buy()
        {
            $offer=request()->param();
            $g_id=$offer['id'];
            $jia = $offer['jia'];
            //没有报名不能出价
//           $baoming=Db::table('bond_orders')
//               ->where('m_id',session('id'))
//               ->where('g_id',$g_id)
//               ->find();
//           if (empty($baoming)){
//                apiResponse('510','没有报名不能出价');
//           }
            //判断商品是否存在
            $isgoods=Db::table('goods')->where('id',$g_id)->find();
            if (empty($isgoods)){
                apiResponse('500','此商品不存在',$isgoods);
            }
            //报名提交提交 保证 金后 等待商品开始拍卖 商品开始拍卖后可以 进行出价
            $res=Db::table('goods')->field('auctionstatus,end_time')->where('id',$g_id)->find();
            if (!empty($res) && $res['auctionstatus']==0){
                apiResponse('400','商品未开始拍卖不能出价',$res);
                }
            if (!empty($res) && time()>strtotime($res['end_time'])){
                apiResponse('400','商品已经结束拍卖不能出价');
                }

            if (empty($jia)){
                apiResponse('405','出价不能为空');
            }

            $phone=Db::table('member')->field('phone')->where('id',session('id'))->find();

              //通过登录会员手机号 查找 后台归属者信息如果有则会员与后台归属者为一个人
            if(empty($phone['phone'])){
//                $isphone=Db::table('artist')->field('phone')->where('phone', $phone['phone'])->find();
//            }else{
                apiResponse('403','请绑定手机号');
            }
                //判断是否能通过 会员id查到 归属者手机号 如果能查到 此会员不能对此商品进行出价
//            if(!empty($isphone)){
//                apiResponse('401','不能对自己的商品进行出价');
//            }
            //判断商品出价是否符合
            $range_price = Db::table('goods')->where('id',$g_id)->value('range_price');
            $starting_price=Db::table('goods')->where('id',$g_id)->value('starting_price');


            //当前商品的最高出价
            $maxp=Db::table('goods_offer')->where('goods_id',$g_id)->max('price');
            //用户自己的的最高出价
            $mymaxp= Db::table('goods_offer')->where('goods_id',$g_id)->where('member_id',session('id'))->max('price');


            if (($jia-$range_price)< $maxp){
                //所出价格 - 出价幅度  不能少于所出价格
                apiResponse('402','出价不能低于出价幅度');
            }
             //判断商品是否有出价
//            if(!empty($maxp)){
//                //如果商品有过出价 出价为最高价
//                $maxprice =$maxp;
//            }else{
//                //如果商品还没有出价起拍价
//                $maxprice=$starting_price;
//            }

              //我的最高出价等于 商品的最高出价
             if (!empty($maxp)&&!empty($mymaxp)&&$mymaxp==$maxp){
                 apiResponse('400','您的出价已经是最高',$mymaxp);
             }else{
                 $data=['member_id'=>session('id'), 'goods_id'=> $g_id, 'price'=>$jia, 'create_time'=>time()];
                 //如果我的出价不是 当前商品最高出价,可以进行出价
                 $ids=Db::table('goods_offer')->insertGetId($data);
//                 $this->chujiamoban($ids);

                 if(!empty($ids)){

                     //修改出价状态 领先 为1
                     $update=['offerstatus'=>0];
                     Db::table('goods_offer')->where('goods_id',$g_id)->update($update);
                     $lingxian=['offerstatus'=>1];
                     Db::table('goods_offer')
                         ->where('goods_id',$g_id)
                         ->where('id',$ids)
                         ->where('member_id',session('id'))
                         ->update($lingxian);
                     //查看出价时间 商品结束时间 ,对比时间 ,如果 出价时间距离 商品结束时间 2 分钟以内 修改商品结束时间,延期商品结束时间

                     $offertime=Db::table('goods_offer')->field('id,create_time,goods_id,member_id')->where('id',$ids)->find();

                     $goodstime=Db::table('goods')->field('id,end_time')->where('id',$offertime['goods_id'])->find();
                       $g_time=strtotime($goodstime['end_time'])-120;
                       $g_endtime=strtotime($goodstime['end_time']);

                     //出价时间 小于结束时间 同时 大于 结束前五分钟时间戳
                     if ($offertime['create_time'] > $g_time && $offertime['create_time'] < $g_endtime){
                        $up=[
                            'end_time'=>date('Y-m-d H:i:s',time()+300),
                        ];

                        $res=Db::table('goods')->where('id',$g_id)->update($up);

                     }


                     //出价成功了去调用发送模版方法 查询 出第二名
                     $this->chujiamoban($ids);
//                     $data1=$offertime;
//                     $data1['jia']=$jia;
                     apiResponse('200','出价成功');
                 }else{
                     apiResponse('500','出价失败');
                 }

             }


        }
        /**
         * 查询被 超过的出价用户
         */
        public function chujiamoban($ids)
        {
//            file_put_contents('1.txt',$ids,8);
            //有用户出价成功后查找 价格被 超过的用户 发送提醒
           $res= Db::table('goods_offer')->where('id',$ids)->find();
//           dump($res);
           if(!empty($res)){
                $maxprice=$res['price'];
               $res1=Db::table('goods_offer')->where('goods_id',$res['goods_id'])->where('member_id','<>',$res['member_id'])->order('price desc')->limit(1)->select();
//               collection()
               if(!empty($res1)){
//            dump( $res1)
                   //$res1 是一个二维数组
                   foreach ($res1  as $k =>$v){
                       $this->sendchujiamoban($v,$maxprice);

                   }
               }
           }
        }
        /**
         * 发送超价模板消息
         */
//        public function sendchujiamoban()
        public function sendchujiamoban($v,$maxprice)
        {
            $res=Db::table('member')->field('nickname,openid')->where('id',$v['member_id'])->find();
            $goods=Db::table('goods')->where('id',$v['goods_id'])->find();


            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . access_token();

//    接受模板消息的用户openid
//            $openid = 'oMXiawvW7cvZPpTBDxGTOPbwzHsQ';
            $openid = $res['openid'];
//        模板消息id
            $template_id = 'WuIlWl1TRUtNlex4i271d-Yo2LkYY8HKQyO24_sKiZ8';
//        设置模板消息
            $array = array();
//        设置接受消息用户的openid
            $array['touser'] = $openid;
//        设置模板消息id
            $array['template_id'] = $template_id;
            $array['url'] = 'http://www.yiluzongheng.com/home/ongoing?id='.$goods['id'].'&g_id='.$goods['id'];
//        设置模板消息
            $data = array();
            $data['first'] = array();
//            $data['first']['value'] = '你的叫价已被超越';
            $data['first']['value'] = '亲爱的'.$res['nickname'].',您的叫价已被超越';
            $data['first']['color'] = '#173177';
            $data['keyword1'] = array();
            $data['keyword1']['value'] = $goods['goods_name'];
            $data['keyword1']['color'] = '#173177';
            $data['keyword2'] = array();
            $data['keyword2']['value'] = $maxprice;
            $data['keyword2']['color'] = '#173177';
            $data['remark'] = array();
            $data['remark']['value'] = '如需再出价，请进入公众号进行叫价,谢谢您对艺陆纵横的支持';
            $data['remark']['color'] = '#173177';
            $array['data'] = $data;
//        调用公共方法curl_post，发送模板消息
            curl_posts($array, $url);
        }
}
