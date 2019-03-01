<?php

namespace app\api\controller;

use think\Db;
use think\session;


class Follow extends Base
{
    public function index()
    {
        //
    }
    /**
     * 显示用户已关注 的店铺列表
     *http://auction.jiangliping.com/api/follow/followlist
     */
    public function followlist()
    {
//        session('id',11);//临时
        $field = [
            'shop.*', 'artist_class.name,followstatus'
        ];
        $list = Db::table('follow')
            ->field($field)
            ->join('shop', 'shop.id=shop_id')
            ->join('artist', 'artist.id=art_id')
            ->join('artist_class', 'artist_class.id=artist.artist_class_id')
            ->where('member_id', session('id'))
            ->where('followstatus',1)
            ->select();


        if ($list) {
            apiResponse(200, '获取关注店铺列表成功', $list);
        }
        apiResponse(400, '获取关注店铺列表失败');
    }

    /**
     * 显示是否关注
     * http://auction.jiangliping.com/api/follow/isfollow
     */
    public function isfollow()
    {
//        session('id',11);//临时
        $shopid=request()->param('id');

        $res = Db::table('follow')->where('shop_id',$shopid)->where('member_id', session('id'))->find();
        if (!empty($res)) {
            apiResponse(200, '已关注', $res);
        }
        apiResponse(400, '未关注',$res);
    }


    /**
     * functoin 关注店铺
     *当点击 关注按键时  调用此方法  将用户在session中保存的ID 和点击按键传递进来的店铺 ID 保存到 关注 表中
     * http://auction.jiangliping.com/api/follow/follow
     * 参数  shop的 id
     */

    public function follow()
    {
//        session('id',11); //临时数据
        //获取当前店铺ID
        $shopid = request()->param('id');
        //当前登录用户的id
        $member_id = session('id');




        $res = Db::table('follow')->where('shop_id',$shopid)->where('member_id', $member_id)->find();


        $res1 = Db::table('shop')->field('id')->where('id',$shopid)->select();
        // 如果用户没有关注 ,点击关注时 进行关注 ,向表中添加 ,用户ID  与 店铺ID
        //如果表中已有关注信息则不在重复关注


      if (empty($res1)){
          apiResponse(404,'找不到此店铺');
          die;
     }
        if (!empty($res) && empty($res['followstatus'])){

                $updata=[
                    'followstatus'=>1
                ];
               $res= Db::table('follow')->where('shop_id',$shopid)->where('member_id',$member_id)->update($updata);
               if($res){
                   apiResponse('200','关注成功',$res);
               }
               apiResponse('400','关注失败');
        }

//        halt($res['followstatus']);

        if (empty($res)) {
//            $this->redirect('/api/wechat/snsapiuserinfo');
            $data = [
                'shop_id' => $shopid,
                'member_id' => $member_id,
                'create_time'=>time(),
                'followstatus'=>1
            ];
            $res1 = Db::table('follow')->insert($data);
//                    $num = Db::table('follow')->where('')->count();
//            Db::table('shop')->where('id',$shopid)->setInc('follownum');

            apiResponse(200, '关注成功', $res1);

        }
        apiResponse(400, '已关注过了');
    }


    /**
     * 取消关注
     * http://auction.jiangliping.com/api/follow/nofollow
     * 参数  shop .id
     */
    public function nofollow()
    {
//        session('id',11);
        $shopid = request()->param('id');
        $member_id = session('id');//临时
    $follow=    Db::table('follow')->where('member_id',$member_id)->where('shop_id',$shopid)->find();
//        halt($follow);
        if($follow){

            $update=[
                'followstatus'=>0
            ];
            $res=DB::table('follow')->where('member_id',$member_id)->where('shop_id',$shopid)->update($update);
        }
//        halt($res);

        if ($res) {
//            Db::table('shop')->where('id',$shopid)->setDec('follownum');

            apiResponse('200', '已取消');
        }
        apiResponse('400', '取消失败');


    }

}