<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class MyAuction extends Base
{

    public function index()
    {
     return view('bidding');
    }
    /**
     * 我的全部竞拍
     * /api/my_auction/myAuction?m_id=3
     */
    public function myAuction(Request $request)
    {
        $param = $request->param();
        //获取个人竞拍表数据
        $goods = Db::table('my_auction')
            ->field('my_auction.goods_id,my_auction.member_id,my_auction.bond_status,my_auction.create_time,start_time,end_time,auctionstatus')
            ->join('goods','my_auction.goods_id = goods.id','LEFT')
            ->where('member_id',session('id'))
            ->order('my_auction.create_time desc')
            ->select();

        $data = [];
        foreach($goods as $k=>$v){
            //获取拍卖开始时间戳
            $end_time = strtotime($v['end_time']);
            //获取拍卖结束时间戳
            $start_time = strtotime($v['start_time']);
            //获取当前时间戳
            $time =time();

            //处理时间
            $endtime = strtotime($v['end_time'])-time();
            // 求整天数
            $enddays = (int)($endtime / (60*60*24));
            // 求剩下的小时数
            $endtime = $endtime % 86400; // 还剩下的秒数
            $endhour = (int)($endtime / (60*60));
            // 求剩下的分钟数
            $endtime = $endtime % (60*60); // 还剩下的秒数
            $endminute = (int)($endtime/ 60);
            // 求剩下的秒数
            $endtime = $endtime % 60;

            //比较时间戳,获取正在进行拍卖的商品
            $data[$k] = Db::table('goods')
                ->field('goods_name,cover_plan,bond,start_time,end_time,starting_price,auctionstatus')
                ->where('id',$v['goods_id'])
                ->find();
            //获取目前竞拍最高价
                $max_price= Db::table('goods_offer')
                ->where('goods_id',$v['goods_id'])
                ->max('price');
            $data[$k]['max_price']=number_format($max_price,2);

            //获取我的最高出价
                $my_price= Db::table('goods_offer')
                ->where('goods_id',$v['goods_id'])
                ->where('member_id',session('id'))
                ->max('price');
            $data[$k]['my_price']=number_format($my_price,2);

            $data[$k]['bond_status'] = $v['bond_status'];
            $data[$k]['member_id'] = $v['member_id'];
            $data[$k]['goods_id'] = $v['goods_id'];

            if($time < $start_time){
                $data[$k]['time'] = '开拍时间'.' '.date('m月d日 H:i',$start_time);
                $data[$k]['offer'] = 0;
            }elseif( $time < $end_time && $time > $start_time ){
                if ($enddays > 0) {
                    $data[$k]['time'] = '距离竞拍结束还有'.' '.($enddays*24+$endhour).'小时'.$endminute.'分钟'.$endtime.'秒' ;
                }else{
                    $data[$k]['time'] = '距离竞拍结束还有'.' '.$endhour.'小时'.$endminute.'分钟'.$endtime.'秒';
                }
                if( $data[$k]['bond_status'] == 1){
                    $data[$k]['offer'] = 1;  // 1代表可以'去出价''
                }else{
                    $data[$k]['offer'] = 0;
                }

            }elseif( $time > $end_time ){
                $data[$k]['time'] = '结束时间'.' '.date('Y-m-d H:i',$end_time);
                $data[$k]['offer'] = 0;
            }
         }
        // p($data);
        if ($data) {
            apiResponse(200,'请求成功',$data);
        }else{
            apiResponse(400,'无记录');
        }
    }


    /**
     * 参拍中
     * /api/my_auction/inAuction?m_id=3
     */
    public function inAuction(Request $request)
    {
//        session('id',11);
        $param = $request->param();
        //获取个人竞拍表数据
        $goods = Db::table('my_auction')
            ->field('my_auction.goods_id,my_auction.member_id,my_auction.bond_status,my_auction.create_time,start_time,end_time,auctionstatus')
            ->join('goods','my_auction.goods_id = goods.id','LEFT')
            ->where('member_id',session('id'))
            ->order('my_auction.create_time desc')
            ->select();

        $data = [];
        foreach($goods as $k=>$v){
            //获取拍卖开始时间戳
            $end_time = strtotime($v['end_time']);
            //获取拍卖结束时间戳
            $start_time = strtotime($v['start_time']);
            //获取当前时间戳
            $time = time();

            //处理时间
            $endtime = strtotime($v['end_time'])-time();
            // 求整天数
            $enddays = (int)($endtime / (60*60*24));
            // 求剩下的小时数
            $endtime = $endtime % 86400; // 还剩下的秒数
            $endhour = (int)($endtime / (60*60));
            // 求剩下的分钟数
            $endtime = $endtime % (60*60); // 还剩下的秒数
            $endminute = (int)($endtime/ 60);
            // 求剩下的秒数
            $endtime = $endtime % 60;

            if($time < $end_time && $time > $start_time || $time< $start_time){
                //比较时间戳,获取正在进行拍卖的商品
                $data[$k] = Db::table('goods')
                    ->field('goods_name,cover_plan,bond,start_time,end_time,starting_price,auctionstatus')
                    ->where('id',$v['goods_id'])
                    ->find();
                //获取目前竞拍最高价
                    $max_price= Db::table('goods_offer')
                    ->where('goods_id',$v['goods_id'])
                    ->max('price');
                $data[$k]['max_price']=number_format($max_price,2);

                //获取我的最高出价
                 $my_price= Db::table('goods_offer')
                    ->where('goods_id',$v['goods_id'])
                    ->where('member_id',session('id'))
                    ->max('price');
                $data[$k]['my_price']=number_format($my_price,2);
                $data[$k]['bond_status'] = $v['bond_status'];
                $data[$k]['member_id'] = $v['member_id'];
                $data[$k]['goods_id'] = $v['goods_id'];

                if($time < $start_time){
                    $data[$k]['time'] = '开拍时间'.' '.date('m月d日 H:i',$start_time);
                    $data[$k]['offer'] = 0;
                }elseif( $time < $end_time && $time > $start_time ){
                    if ($enddays > 0) {
                        $data[$k]['time'] = '距离竞拍结束还有'.' '.($enddays*24+$endhour).'小时'.$endminute.'分钟'.$endtime.'秒' ;
                    }else{
                        $data[$k]['time'] = '距离竞拍结束还有'.' '.$endhour.'小时'.$endminute.'分钟'.$endtime.'秒';
                    }
                    if( $data[$k]['bond_status'] == 1){
                        $data[$k]['offer'] = 1;  // 1代表可以'去出价''
                    }else{
                        $data[$k]['offer'] = 0;
                    }
                }
            }
         }
        if ($data) {
            apiResponse(200,'请求成功',array_values($data));
        }else{
            apiResponse(400,'无记录');
        }
    }


    /**
     * 已获拍
     */
    public function yesAuction(Request $request)
    {

//        session('id',33);
        $param = $request->param();
        //获取个人竞拍表数据
        $goods = Db::table('my_auction')
            ->field('my_auction.goods_id,my_auction.member_id,my_auction.bond_status,my_auction.create_time,start_time,end_time,auctionstatus')
            ->join('goods','my_auction.goods_id = goods.id','LEFT')
            ->where('member_id',session('id'))
            ->order('my_auction.create_time desc')
            ->select();

        $data = [];
        $data1 = [];
        foreach($goods as $k=>$v){
            //获取拍卖开始时间戳
            $end_time = strtotime($v['end_time']);
            //获取拍卖结束时间戳
            $start_time = strtotime($v['start_time']);
            //获取当前时间戳
            $time = time();

            $data[$k] = Db::table('goods')
                ->field('goods_name,cover_plan,bond,start_time,end_time')
                ->where('id',$v['goods_id'])
                ->find();
            //获取目前竞拍最高价
                $max_price= Db::table('goods_offer')
                ->where('goods_id',$v['goods_id'])
                ->max('price');
            $data[$k]['max_price']=number_format($max_price,2);

             //获取我的最高出价
                $my_price= Db::table('goods_offer')
                ->where('goods_id',$v['goods_id'])
                ->where('member_id',session('id'))
                ->max('price');
            $data[$k]['my_price']=number_format($my_price,2);


            //比较时间戳,当前时间大于拍品结束时间,用户出价等于最高价时,获拍
            if ($time > $end_time) {
                $res= Db::table('goods_offer')
                    ->field('goods_offer.id,goods_offer.member_id,goods_offer.goods_id,goods_offer.price,goods_name,bond,end_time,cover_plan,auctionstatus')
                    ->join('goods','goods_offer.goods_id = goods.id','LEFT')
                    ->where('goods_offer.member_id',$v['member_id'])
                    ->where('goods_offer.goods_id',$v['goods_id'])
                    ->where('price','>',0)
                    ->where('price',$data[$k]['max_price'])
                    ->find();

                if($res){
                    $res['bond_status'] = $v['bond_status'];
                    $data1[] = $res;
                }
            }
        }
        // p($data1);
        if (!empty($data1)) {
            apiResponse(200,'请求成功',$data1);
        }else{
            apiResponse(400,'无记录');
        }
    }


    /**
     * 未获拍
     */
    public function noAuction(Request $request)
    {
        $param = $request->param();
        //获取个人竞拍表数据
        $goods = Db::table('my_auction')
            ->field('my_auction.goods_id,my_auction.member_id,my_auction.bond_status,my_auction.create_time,start_time,end_time,auctionstatus')
            ->join('goods','my_auction.goods_id = goods.id','LEFT')
            ->where('member_id',session('id'))
            ->order('my_auction.create_time desc')
            ->select();
//   dump($goods);
        $data = [];
        $data1 = [];
        foreach($goods as $k=>$v){
            //获取拍卖结束时间戳
            $end_time = strtotime($v['end_time']);
            //获取当前时间戳
            $time = time();

            $data[$k] = Db::table('goods')
                ->field('goods_name,cover_plan,bond,start_time,end_time')
                ->where('id',$v['goods_id'])
                ->find();

            //获取目前竞拍最高价
                $max_price= Db::table('goods_offer')
                ->where('goods_id',$v['goods_id'])
                ->max('price');
            $data[$k]['max_price']=number_format($max_price,2);
            //获取我的最高出价
                $my_price= Db::table('goods_offer')
                ->where('goods_id',$v['goods_id'])
                ->where('member_id',session('id'))
                ->max('price');
            $data[$k]['my_price']=number_format($my_price,2);
            //比较时间戳,当前时间大于拍品结束时间,用户出的最高价小于此商品最高价时,未获拍
            if ($time > $end_time) {
                $res = Db::table('goods_offer')
                    ->field('goods_offer.id,goods_offer.member_id,goods_offer.goods_id,goods_name,bond,end_time,cover_plan')
                    ->join('goods','goods_offer.goods_id = goods.id','LEFT')
                    ->where('goods_offer.member_id',$v['member_id'])
                    ->where('goods_offer.goods_id',$v['goods_id'])
                    ->where('price',$data[$k]['my_price'])
                    ->where('price','<',$data[$k]['max_price'])
                    ->find();
                if($res){
                    $res['bond_status'] = $v['bond_status'];
                    $res['price'] = $data[$k]['max_price'];
                    $data1[] = $res;
                }
            }

            // $data[$k]['bond_status'] = $v['bond_status'];

        }
        // if($time > $end_time){
        //     if ($data[$k]['my_price'] < $data[$k]['max_price']) {
        //         $res = $data;
        //         p($res);
        //         apiResponse(200,'请求成功',$res);

        //     }
        // }
//         p($data1);
        if (!empty($data1)) {
            apiResponse(200,'请求成功',$data1);
        }else{
            apiResponse(400,'无记录');
        }
    }
}
