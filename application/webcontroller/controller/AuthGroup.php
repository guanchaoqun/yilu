<?php
/**
 * 角色管理
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 17:57
 */

namespace app\webcontroller\controller;


use app\common\model\AuthGroupAccess;
use think\Db;

class AuthGroup extends Common
{
    private $cModel;   //当前控制器关联模型

    public function _initialize()
    {
        parent::_initialize();
        $this->cModel = new \app\common\model\AuthGroup();
    }

    /**
     * 角色信息
     * @return mixed
     */
    public function index()
    {
        $dataList = $this->cModel->select();

        $this->assign('dataList', $dataList);

        return $this->fetch();
    }

    public function add()
    {
        if (request()->isPost()){
            $data = input('post.');

            $data['rules'] = $data['rules'] ? implode(',', $data['rules']) : '';
            if(!$this->isunique($this->cModel,['title'=>$data['title']])){
                return $this->result('', 400, '角色名称已存在');
            }
            $result = $this->cModel->validate(TRUE)->allowField(true)->save($data);
            if ($result == FALSE) {
                $this->result('', 400, $this->cModel->getError());
            }
            $this->result('', 200, '新增成功！');
        }else{
            $arModel = new \app\common\model\AuthRule();
            $authRuleTree = $arModel->treeList();

            //数组型对象转换成数组
            $authRuleTree=collection($authRuleTree)->toArray();

            //数据重组
            $b=[];$k1=0;$k2=0;
            foreach ($authRuleTree as $k=>$v){
                $v['_NODE_']=[];
                switch ($v['h_layer']){
                    case 1:
                        $k1=$k;
                        $b[$k]=$v;
                        break;
                    case 2:
                        $k2=$k;
                        $b[$k1]['_NODE_'][$k]=$v;
                        break;
                    case 3:
                        $b[$k1]['_NODE_'][$k2]['_NODE_'][]=$v;
                        break;
                    default :
                }
            }

            $this->assign('authRuleTree', $b);   //树形权限节点列表
            return $this->fetch();
        }
    }

    public function edit($id)
    {
        if (request()->isPost()){
            $data = input('post.');

            if ( isset($data['rules']) ){
                $data['rules'] = $data['rules'] ? implode(',', $data['rules']) : '';
            }
            if(!$this->isunique($this->cModel,['title'=>$data['title']],$data['id'])){
                return $this->result('', 400, '角色名称已存在');
            }
            $result = $this->cModel->validate(TRUE)->allowField(true)->save($data, $data['id']);

            if ($result === FALSE) {
                $this->result('', 400, $this->cModel->getError());
            }

            $this->result('', 200, '编辑成功！');
        }else{
            $data = $this->cModel->get($id);
//            $data = db('auth_group')->where('id',$id)->find();
//            p($data);die;
            $this->assign('data', $data);

            $arModel = new \app\common\model\AuthRule();
            $authRuleTree = $arModel->treeList('', 1);   //树形权限节点列表
//            p($authRuleTree);die;
            $rulesArr = explode(',', $data['rules']);   //以前就拥有的权限节点
            //数组型对象转换成数组
            $authRuleTree=collection($authRuleTree)->toArray();

            //数据重组
            $b=[];$k1=0;$k2=0;
            foreach ($authRuleTree as $k=>$v){
                $v['ischeck']=in_array($v['id'], $rulesArr)?'y':'n';
                $v['_NODE_']=[];
                switch ($v['h_layer']){
                    case 1:
                        $k1=$k;
                        $b[$k]=$v;
                        break;
                    case 2:
                        $k2=$k;
                        $b[$k1]['_NODE_'][$k]=$v;
                        break;
                    case 3:
                        $b[$k1]['_NODE_'][$k2]['_NODE_'][]=$v;
                        break;
                    default :
                }
            }
            $this->assign('authRuleTree', $b);

            return $this->fetch();
        }
    }

    public function delete()
    {
        if (request()->isPost()){
            $id = input('id');
            if (isset($id) && !empty($id)){
                $id_arr = explode(',', $id);
                $where = [ 'id' => ['in', $id_arr] ];
                $result = $this->cModel->where($where)->delete();

                $where = [ 'group_id' => ['in', $id_arr] ];
                $agaModel = new AuthGroupAccess();
                $agaModel->where($where)->delete();
                if ($result == FALSE) {
                    $this->result('', 400, $this->cModel->getError());
                }
                $this->result('', 200, '删除成功！');
            }
        }
    }

    //public function authUser($id)
    //{
    //    $agaModel = new AuthGroupAccess();
    //    if (request()->isPost()){
    //        $data = input('post.');
    //        $group_id = $data['id'];   //当前角色ID
    //        $uid = $data['uid'];   //新提交授权用户数组:[1,2,3,4....]
    //
    //        $oldData = $agaModel->where(['group_id' => $group_id])->select();
    //        $oldUser = array();   //以前授权用户
    //        $mixArr = array();   //交集授权用户
    //        $addArr = array();   //新增授权用户
    //        $delArr = array();   //删除授权用户
    //        foreach ($oldData as $k =>$v){
    //            $oldUser[] = $v['uid'];
    //        }
    //        $mixArr = array_intersect($uid, $oldUser);
    //        if (empty($mixArr)){
    //            $addArr = $uid;
    //            $delArr = $oldUser;
    //        }else{
    //            $addArr = array_diff($uid, $mixArr);
    //            $delArr = array_diff($oldUser, $mixArr);
    //        }
    //        if (!empty($delArr)){
    //            $where = [
    //                'group_id' => $group_id,
    //                'uid' => ['in', $delArr],
    //            ];
    //            $agaModel->where($where)->delete();
    //        }
    //        if (!empty($addArr)){
    //            $addList = array();
    //            foreach ($addArr as $k => $v){
    //                $addList[] = ['group_id' => $group_id, 'uid' => $v];
    //            }
    //            $agaModel->saveAll($addList, false);
    //        }
    //        return ajaxReturn(lang('action_success'), url('index'));
    //    }else{
    //        $authList = $agaModel->alias('a')->join('user u','a.uid = u.id')
    //            ->field('u.id,u.username,u.name')
    //            ->where(['group_id' => $id])->select();   //已经拥有权限用户
    //
    //        $uModel = new User();
    //        $userList = $uModel->field('id,username,name')->select();   //全部用户
    //
    //        foreach ($userList as $k => $v){   //删除全部用户中已授权用户
    //            foreach ($authList as $k2 => $v2){
    //                if ($v['id'] == $v2['id']){
    //                    unset($userList[$k]);
    //                    break;
    //                }
    //            }
    //        }
    //        $this->assign('id', $id);
    //        $this->assign('userList', $userList);
    //        $this->assign('authList', $authList);
    //        return $this->fetch();
    //    }
    //}
}