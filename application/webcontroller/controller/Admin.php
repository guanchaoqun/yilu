<?php
/**
 * 管理员管理
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 17:58
 */

namespace app\webcontroller\controller;

use think\image;
use app\common\model\AuthGroupAccess;
class Admin extends Common
{
    /**
     * 管理员信息
     */
    public function index()
    {
        $data = model('admin')->field('username,nick_name,link_phone,email,admin_id,status')->select();
//        p($data);die;
        $this->assign('data', $data);

        return $this->fetch();
    }

    public function adminEdit(){

        if($this->request->post()){
            if(!empty($_POST['nick_name'])){ cookie('name', $_POST['nick_name']); }
            $_POST['password']=md5(substr(md5($this->request->post('password')),10,10).'dch');
            model('admin')->allowField(true)->save($_POST,['admin_id'=>UID]);
            return TRUE;
        }
        $data=model('admin')->where('admin_id',UID)->find();

        $this->assign('data',$data);
        return $this->fetch();
    }

    /**
     * 新增管理员
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $data       = $this->request->param();
            $adminModel = new \app\common\model\Admin();

            $data['password'] = md5(substr(md5($this->request->post('password')), 10, 10) . 'dch');

            $a = $adminModel->allowField(TRUE)->save($data);

            if ($a == FALSE) {
                $this->result('', 400, $adminModel->getError());
            }

            $this->result('',200,'管理员新增成功！');
        }

        return $this->fetch();
    }

    /**
     * 密码重置
     */
    public function passwordReset()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('admin_id');

            $password = md5(substr(md5(123456), 10, 10) . 'dch');

            $adminModel = new \app\common\model\Admin();

            $result = $adminModel->isUpdate(TRUE)->save(['admin_id' => $id, 'password' => $password]);

            if ($result === FALSE) {
                $this->result('', 400, $adminModel->getError());
            }

            $this->result('', 200, '重置成功！');

        }
    }

    /**
     * 状态更新
     */
    public function adminStatus()
    {
        if($this->request->isPost()){
            $data=$this->request->param();
            $adminModel = new \app\common\model\Admin();

            $result = $adminModel->isUpdate(TRUE)->save($data);

            if ($result == FALSE) {
                $this->result('', 400, $adminModel->getError());
            }
            $this->result('', 200, '更新成功！');
        }
    }

    /**
     * 授权角色
     */
    public function setAuth($id){
        $agaModel = new AuthGroupAccess();
        if($this->request->isPost()){
            $group_id = $this->request->param();

            $where = ['uid' => $id];
            $agaModel->where($where)->delete();
            if (!empty($group_id['group_id'])){
                $addList = array();
                foreach ($group_id['group_id'] as $k =>$v){
                    $addList[] = ['uid' => $id, 'group_id' => $v];
                }
                $agaModel->saveAll($addList, false);
            }
            $this->result('', 200, '授权成功！');
        }
        $agModel = new \app\common\model\AuthGroup();
        $groupList = $agModel->where(['status' => 1])->field('id,title')->order('module ASC,level ASC,id ASC')->select();   //所有正常角色
        $userGroup = $agaModel->where(['uid' => $id])->column('group_id');   //当前用户已拥有角色
        foreach ($groupList as $k => $v){
            if(in_array($v['id'],$userGroup)){
                $groupList[$k]['ischeck'] = 'y';
            }
        }
        $nickname = (new \app\common\model\Admin())->where('admin_id='.$id)->value('nick_name');
        $this->assign('nickname', $nickname);
        $this->assign('groupList', $groupList);

        return $this->fetch();
    }

    /**
     * 管理员删除
     */
    public function delete(){

        model('admin')->where('admin_id',$_POST['admin_id'])->delete();

        $this->result('',200,'删除成功');
    }
}