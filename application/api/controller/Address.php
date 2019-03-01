<?php

namespace app\api\controller;

use Symfony\Component\Yaml\Tests\B;
use think\Controller;
use think\Db;
use think\Request;

class address extends Base
{
    /**
     * @return \think\response\View
     * 地址 管理页面
     */
    public function addressManag()
    {
        return view('addressManag');
    }

    /**
     * 新增地址  渲染页面
     *
     */
    public function newAddress()
    {
        return view('newAddress');
    }

    /**
     * @return \think\response\View
     * 省页面
     */
    public function addressList()
    {
        return view('addressList');
    }
    /**
     * 市 页面
     */
    public function addressShi()
    {
        return  view('addressShi');
     }
    /**
     * 区县页面
     */
    public function addressXian()
    {
        return view('addressXian');
    }
    /**
     * 修改页面
     */
    public function xiugaiAddress()
    {
        return view('xiugaiAddress');
    }

    /**
     * 修改页面
     */
    public function addressList1()
    {
        return view('addressList1');
    }

    /**
     * @return \think\response\View
     * 修改市 页面
     */
    public function addressShi2()
    {
        return view('addressShi2');
    }
    /**
     * 修改 县
     */
    public function addressXian3()
    {
        return view('addressXian3');
    }

    /**
     * 获取用户地址列表
     *http://auction.jiangliping.com/api/address/address_list?m_id=8
     * /api/address/address_list?m_id=8
     */
    public function address_list()
    {
        $mid =session('id');
        $data = Db::table('address')
            ->field('id,name,phone,area,address,state')
            ->where('m_id', $mid)
            ->select();
        if (!empty($data)) {
            apiResponse(200, '请求成功', $data);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 添加地址
     * /api/address/address_add?name=张三&m_id=3&phone=13888888888&area=山东&address=济南
     */
    public function address_add(Request $request)
    {
        $post = $request->param();
//        if (empty($post['m_id'] && $post['name'] && $post['phone'] && $post['area'] && $post['address'])) {
        if (empty($post['name']) && empty($post['phone']) && empty($post['area']) && empty($post['address'])) {
            apiResponse(400, '请填写所有信息');
        }

        //判断是否是第一次添加地址 是-1(设置为默认地址) 不是-0
        $res = Db::table('address')
            ->where('m_id', session('id'))
            ->select();

        if (empty($res)) {
            $state = 1;
        } else {
            $state = 0;
        }

        $data = [
            'm_id' => session('id'),
            'name' => $post['name'],
            'phone' => $post['phone'],
            'area' => $post['area'],
            'state' => $state,
            'address' => $post['address'],
            'create_time' => time()
        ];

        $add = Db::table('address')
            ->insert($data);

        if (!empty($add)) {
            apiResponse(200, '添加地址成功');
        } else {
            apiResponse(400, '添加地址失败');
        }
    }

    /**
     * 删除地址
     * /api/address/address_del?id=
     */
    public function address_del(Request $request)
    {
        $id = $request->param();

        $data = Db::table('address')
            ->where('id', $id['id'])
            ->delete();

        if (!empty($data)) {
            apiResponse(200, '删除成功');
        } else {
            apiResponse(400, '删除失败');
        }
    }

    /**
     * 修改地址 - 显示要修改的地址
     * /api/address/address_edit?id=
     */
    public function address_edit(Request $request)
    {
        $post = $request->param();

        $data = Db::table('address')
            ->field('id,name,phone,area,address')
            ->where('id', $post['id'])
            ->find();

        if (!empty($data)) {
            apiResponse(200, '请求成功', array($data));
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 修改地址 - 执行修改
     * /api/address/address_update?id=32&name=张三&phone&area=山东&address=济南
     */
    public function address_update(Request $request)
    {

        $post = $request->param();
//        file_put_contents('add.txt',$post);
        if (empty($post['name']) && empty($post['phone']) && empty($post['area']) && empty($post['address'])) {
            apiResponse(400, '请填写所有信息');
        }

        $data = [
            'name' => $post['name'],
            'phone' => $post['phone'],
            'area' => $post['area'],
            'address' => $post['address'],
            'update_time' => time()
        ];

        $res = Db::table('address')
            ->where('id', $post['id'])
            ->update($data);

        if (!empty($res)) {
            apiResponse(200, '修改成功');
        } else {
            apiResponse(400, '修改失败');
        }
    }

    /**
     * 修改默认收货地址
     * /api/address/address_default?m_id=3&id=36
     */
    public function address_default(Request $request)
    {
        $id = $request->param();
        //把已存在默认地址取消
        $res = Db::table('address')
            ->where('m_id', session('id'))
            ->where('state', 1)
            ->update(['state' => 0]);

        //设置新的默认地址
        $res1 = Db::table('address')
            ->where('id', $id['id'])
            ->update(['state' => 1]);

        if (!empty($res1)) {
            apiResponse(200, '修改默认地址成功');
        } else {
            apiResponse(400, '修改默认地址失败');
        }
    }

    /**
     * 添加地区三级联动 - 获取省
     */
    public function sheng()
    {
        $data = Db::table('area')->field('area_id,name')->where('parentid', 0)->select();

        if (!empty($data)) {
            apiResponse(200, '请求成功(省)', $data);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 添加地址三级联动 - 获取市
     */
    public function shi(Request $request)
    {
        //根据ID获取省级数据
        $id = $request->param();

        $data = Db::table('area')
            ->where('area_id', $id['area_id'])
            ->select();

        //根据省数据获取areaid 然后获取 parenid = areaid的市级数据
        foreach ($data as $v) {
            $data1 = Db::table('area')
                ->where('parentid', $v['areaid'])
                ->select();
        }

        if (!empty($data)) {
            apiResponse(200, '请求成功(市)', $data1);
        } else {
            apiResponse(400, '请求失败');
        }
    }

    /**
     * 添加地址三级联动 - 获取区、县
     */
    public function qu(Request $request)
    {
        //根据ID获取市级数据
        $id = $request->param();

        $data = Db::table('area')
            ->where('area_id', $id['area_id'])
            ->select();

        //根据市数据获取areaid 然后获取 parenid = areaid的区级数据
        foreach ($data as $v) {
            $data1 = Db::table('area')
                ->where('parentid', $v['areaid'])
                ->select();
        }

        if (!empty($data)) {
            apiResponse(200, '请求成功(区)', $data1);
        } else {
            apiResponse(400, '请求失败');
        }
    }
}
