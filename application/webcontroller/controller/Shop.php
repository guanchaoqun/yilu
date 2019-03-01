<?php
namespace app\webcontroller\controller;
use think\Db;

class Shop extends Common
{
    private $model;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        $this->model=new \app\common\model\Shop();
    }

    /**
     * 商店管理列表页
     * @param $request
     * @return json
     */
    public function index(){
        //定义数据表格
        $table = [
            ['type' => 'numbers', 'title' => '序号'],
            ['field' => 'shopname','title' => '商店名称','align'=>'center','width' => 200,],
            ['field' => 'status','title' => '状态','align'=>'center','width' => 100,'templet' => '#tb-status'],
            ['field' => 'create_time','title' => '创建时间','align'=>'center','width' => 200,],
            ['field' => 'image', 'title' =>'封面图','align'=>'center', 'width' => 100,'templet' => '#tb-image'],
            //操作标签
            ['fixed'   => 'right','title'   => '操作','width'   => 178,'align'   => 'center','toolbar' => '#tb-action',],
        ];

        $this->assign('table', json_encode($table));

        return $this->fetch();
    }
    /**
     * 获取数据
     * @param $request
     * @return json
     */
    public function getData($limit)
    {
        $request = input();
        if(!empty($request['key'])){
            $data = $this->model
                ->where('shaopname','like',"%".$request['key']['nickname']."%")
                ->whereOr('phone',$request['key']['nickname'])
                ->order('create_time desc')
                ->paginate($limit)
                ->toArray();
        }else{
            $data = $this->model->order('create_time desc')->paginate($limit)->toArray();
        }
//        p($data);die;

        return $this->result_layui($data['data'], 200, '分类信息', $data['total']);
    }

    /**
     * 新增商店功能
     */
    public function create()
    {
        // 判断当前操作
        if ($this->request->isAjax()) {

            $request = input();

            return $this->addAction($request, $this->model);

        }
        $data=Db::table('shop')->field('art_id')->select();

        foreach ($data as $k => $v){
            $a[]=$v;
        }
        $res=array_column($a,'art_id');

        $artist=Db::table('artist')->where('id','not in',$res)->select();



        return $this->fetch('createEdit',['artist'=>$artist]);
    }
    /**
     * 编辑
     * @param $request
     * @return json
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $request = input();

            return $this->editActon($request, $this->model);

        }

        $data = $this->model->find($id);


        $artist=Db::table('artist')->where('artist.id','=',$data['art_id'])->select();


        $this->assign(['data'=> $data,'artist'=>$artist]);

        return $this->fetch('createEdit');
    }
    /**
     * 删除商店
     * @param $request
     * @return json
     */
    public function delete()
    {
        if ($this->request->isAjax() && $this->request->has('id')) {
            $id = $this->request->post('id');

            $data = $this->model->get($id);

            if (!$data) {
                return $this->result('', 400, '当前数据不存在！删除失败');
            }

            $result = $data->delete();

            if ($result == FALSE) {
                return $this->result('', 400, $data->getError());
            }

            return $this->result('', 200, '删除成功');
        }

        return $this->result('', 400, '删除失败');
    }

    /**
     * 编辑状态
     * @param $request
     * @return json
     */
    public function updatestate(){
        if ($this->request->isAjax() && $this->request->has('id')) {
            $data = $this->request->post();
            $this->model->where('id', $data['id'])->update(['status' => $data['status']]);
            return $this->result('', 200, '修改成功');
        }
        return $this->result('', 400, '修改失败');
    }


}