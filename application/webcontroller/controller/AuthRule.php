<?php
/**
 * 权限节点管理
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 17:53
 */

namespace app\webcontroller\controller;


class AuthRule extends Common
{
    private $cModel;   //当前控制器关联模型

    public function _initialize()
    {
        parent::_initialize();
        $this->cModel = new \app\common\model\AuthRule();
    }

    /**
     * 权限节点列表
     * @return mixed
     */
    public function index()
    {
        $dataList = $this->cModel->treeList();

        $this->assign('dataList', $dataList);

        return $this->fetch();
    }

    /**
     * 增加权限节点
     * @return mixed
     */
    public function create()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $result = $this->cModel->validate(CONTROLLER_NAME . '.add')->allowField(TRUE)->save($data);
            if ($result) {
                $this->result('', 200, '新增成功！');
            } else {
                $this->result('', 400, $this->cModel->getError());
            }
        } else {
            $treeList = $this->cModel->treeList('webcontroller');

            $this->assign('treeList', $treeList);

            return $this->fetch();
        }
    }

    /**
     * 编辑权限节点
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        if (request()->isPost()) {
            $data = input('post.');
            if (count($data) == 2) {
                foreach ($data as $k => $v) {
                    $fv = $k != 'id' ? $k : '';
                }
                $result = $this->cModel->validate(CONTROLLER_NAME . '.' . $fv)->allowField(TRUE)->save($data, $data['id']);
            } else {
                $result = $this->cModel->validate(CONTROLLER_NAME . '.edit')->allowField(TRUE)->save($data, $data['id']);
            }
            if ($result === FALSE) {
                $this->result('', 400, $this->cModel->getError());
            }
            $this->result('', 200, '编辑成功！');
        } else {
            $data = $this->cModel->get($id);
            $this->assign('data', $data);

            $treeList = $this->cModel->treeList();
            $this->assign('treeList', $treeList);

            return $this->fetch();
        }
    }

    /**
     * 删除权限节点
     */
    public function delete()
    {
        if (request()->isPost()) {
            $id = input('id');
            if (isset($id) && !empty($id)) {
                $id_arr = explode(',', $id);
                $where  = ['id' => ['in', $id_arr]];
                $result = $this->cModel->where($where)->delete();
                if ($result == FALSE) {
                    $this->result('', 400, $this->cModel->getError());
                }

                $this->result('', 200, '删除成功！');
            }
        }

    }
}