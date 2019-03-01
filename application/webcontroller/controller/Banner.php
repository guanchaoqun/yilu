<?php

namespace app\webcontroller\controller;

use think\Controller;
use think\Request;

class Banner extends Common
{
  private $model;

  public function _initialize()
  {
    parent::_initialize();

    $this->model=new \app\common\model\Banner();
  }

  public function index()
  {
    // 定义数据表格
    $table = [
        ['type' => 'numbers','title' => '序号',],
        ['field' => 'banner_name','title' => '名称','align' => 'center','width' => 180,],
        ['field' => 'banner_img', 'title' => '图片','align' => 'center', 'width' => 150,'templet' => '#tb-image'],
        ['field' => 'start_time','title' => '开始时间','align' => 'center','width' => 200,],
        ['field' => 'end_time','title' => '结束时间','align' => 'center','width' => 200,],
        ['field' => 'banner_url','title' => '链接','align' => 'center','width' => 200,],
        ['field' => 'status','title' => '状态','align'=>'center','width' => 60,'templet' => '#tb-status'],
        //操作标签
        ['title' => '操作','width' => 178,'align' => 'center','toolbar' => '#tb-action',],
     ];

     $this->assign('table', json_encode($table));

     return $this->fetch();
  }

    /**
     * 数据列表
     * @param string
     * @return json
     */
    public function getData($limit)
    {
        $name = '';
        $data = $this->request->get();
        if(!empty($data['key']['name'])){
            $name = trim($data['key']['name']);
        }
        if($name){
            $data = $this->model->where('name','like',$name.'%')->paginate($limit)->toArray();

        }else{
            $data = $this->model->paginate($limit)->toArray();
        }
        return $this->result_layui($data['data'], 200, '分类信息', $data['total']);
    }

    /**
     * 新增功能
     */
    public function create()
    {
        // 判断当前操作
        if ($this->request->isAjax()) {
            $request = input();
            // if(!empty($request['banner_img'])){
            //     $request['banner_img'] = implode(',',$request['banner_img']);
            // }
            return $this->addAction($request, $this->model);
        }
        return $this->fetch('createEdit');
    }

    /**
     * 编辑功能
     */
    public function edit($id)
    {
        if ($this->request->isAjax()) {
            $request = input();
            // if(!empty($request['image'])){
            //     $request['image'] = implode(',',$request['image']);
            // }
            return $this->editActon($request, $this->model);
        }
        $data = $this->model->find($id);
        // $data['image'] = explode(',',$data['image']);
        $this->assign('data', $data);
        return $this->fetch('createEdit');
    }

    /**
     * 删除
     */
    public function delete($id)
    {
        return $this->delAction($id, $this->model);
        
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
