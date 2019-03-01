<?php
/**
 * 公共类
 * Created by PhpStorm.
 * User: GUO
 * Date: 2017/12/31
 * Time: 19:25
 */

namespace app\webcontroller\controller;

use expand\Auth;
use think\Config;
use think\Lang;
use think\Session;
use think\Response;
use think\exception\HttpResponseException;
class Common extends Base
{

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        if(!Session::get('userId')){
            $this->redirect('login/index');
        }

        $userId = Session::get('userId');
        //设置登陆用户ID常量
        define('UID', $userId);
        //获取菜单信息
        $treeMenu = $this->treeMenu();
        //当访问首页时分配菜单信息
        if('Index/index'==CONTROLLER_NAME . '/' . ACTION_NAME){
            $this->assign('treeMenu', $treeMenu);
        }

        //跳过权限
        $jump_auth = [
            'Index/index',
            'Common/upload',
            'Index/autherror',
            'Index/content',
            'Admin/adminedit',
            'Index/cleancache',

        ];

        if (in_array(CONTROLLER_NAME . '/' . ACTION_NAME, $jump_auth) || in_array(ACTION_NAME,['getdata'])) {
            return TRUE;
        }
        //权限验证
        $auth = new Auth();
        if (!$auth->check(CONTROLLER_NAME . '/' . ACTION_NAME, UID) && Config::get('is_auth_check')) {
            if ($this->request->isGet()) {
                $this->redirect('index/autherror');
            } else {
                $this->result('', 400, '没有操作权限');
            }
        }
    }

    /**
     * 权限菜单
     */
    public function treeMenu()
    {
        $treeMenu = cache('DB_TREE_MENU_' . UID);

        if (!$treeMenu) {
            $where = [
                'ismenu' => 1,
                'module' => 'webcontroller',
            ];

            if (UID != '-1') {
                $where['status'] = 1;
            }

            $arModel = new \app\common\model\AuthRule();

            $lists     = $arModel->where($where)->order('sorts ASC,id ASC')->select();

            foreach ($lists as $k=>$v){
                parse_str($v['name_additional'],$additional);
                $lists[$k]['name_additional']=$additional;
            }

            $treeClass = new \expand\Tree();
            $treeMenu  = $treeClass->create($lists);
            //判断导航tree用户使用权限
            foreach ($treeMenu as $k => $val) {
                if (authcheck($val['name'], UID) == 'noauth') {
                    unset($treeMenu[ $k ]);
                }
            }
            //缓存菜单信息
            cache('DB_TREE_MENU_' . UID, $treeMenu);
        }

        return $treeMenu;
    }

    /**
     * 返回封装后的API数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param integer $code 返回的code
     * @param mixed $msg 提示信息
     * @param string $type 返回数据格式
     * @param array $header 发送的Header信息
     * @return void
     */
    protected function result_layui($data, $code = 0, $msg = '', $count = 0, $type = '', array $header = [])
    {
        $result = [
            'code'  => $code,
            'msg'   => $msg,
            'count' => $count,
            'time'  => $this->request->server('REQUEST_TIME'),
            'data'  => $data,
        ];
        $type = $type ?: $this->getResponseType();
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /**
     * 文件上传
     */
    public function upload(){
        $_FILES['Filedata']['name'] = $this->request->post('filename');

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        //本地上传目录设置
        
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');

        if ($info) {
            return ['code' => 200, 'content' => '/uploads/' . str_replace('\\', '/', $info->getSaveName()), 'msg' => '上传成功！'];
        } else {
            // 上传失败获取错误信息
            return ['code' => 400, 'msg' => $file->getError()];
        }
    }

    public function imgupload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');

        if ($info) {

            $this->result(['src'=>'/uploads/' . str_replace('\\', '/', $info->getSaveName())],200,'上传成功');

        } else {
            // 上传失败获取错误信息
            $this->result('',400,$file->getError());
        }
    }
    /**
     * 新增/
     * @param $data
     */
    protected function addAction(array $data,$model)
    {
        $result = $model->validate(CONTROLLER_NAME . '.add')->allowField(TRUE)->isUpdate(FALSE)->save($data);

        if ($result) {

            return $this->result_success(Lang::get('新增成功'));
        }

        return $this->result_error($model->getError());
    }

    /**
     * 编辑方法
     * @param $data 编辑的数据
     * @param $model    模型
     */
    protected function editActon($data, $model)
    {
        if (count($data) == 2) {
            $fv = '';

            foreach ($data as $k => $v) {
                $fv = $k != 'id' ? $k : '';
            }

            $result = $model->validate(CONTROLLER_NAME . '.' . $fv)->allowField(TRUE)->isUpdate(TRUE)->save($data);

        } else {
            $result = $model->validate(CONTROLLER_NAME . '.edit')->allowField(TRUE)->isUpdate(TRUE)->save($data);
        }
        //判断编辑结果
        if ($result === FALSE) {
            //返回错误信息
            return $this->result_error($model->getError());
        }
        //返回成功消息
        return $this->result_success(Lang::get('编辑成功'));
    }

    /**
     * 删除方法
     * @param $id
     * @param $model
     */
    protected function delAction($id, $model)
    {
        //请求验证
        if($this->request->isAjax() && $id){

            $data = $model->get($id);

            if (!$data) {
                return $this->result_error(Lang::get('del_data_null'));
            }

            if ($data['status'] == 1 && isset($data['status'])) {
                return $this->result_error(Lang::get('启用状态不能删除'));
            }

            $result = $data->delete();

            if ($result == FALSE) {
                return $this->result_error($model->getError());
            }

            return $this->result_success(Lang::get('删除成功'));
        }
        //错误提示
        return $this->fetch('index/actionError');
    }

    /**
     * 获取数据
     * @param        $model 模型
     * @param        $limit 获取数据数量
     * @param string $field 获取字段
     */
    protected function getDataAction($model, $limit, $field = '')
    {
        //请求验证
        if ($this->request->isAjax()) {

            if($field){
                $data = $model->field($field)->paginate($limit);
            }else{
                $data = $model->paginate($limit);
            }

            $data = $data->toArray();

            return $this->result_layui($data['data'], 0, Lang::get('page_data'), $data['total']);
        }
        //错误提示
        return $this->fetch('index/actionError');
    }


    public function isunique($model,$where,$id=''){
        if($id){
            $res = $model->where($where)->where('id','NEQ',$id)->find();
        }else{
            $res = $model->where($where)->find();
        }
        if(!empty($res)){
            return false;
        }
        return true;
    }

    public function downloadexcel($data,$tableheader,$filename)
    {

        include VENDOR_PATH."phpexcel/Classes/PHPExcel.php";
        include VENDOR_PATH."phpexcel/Classes/PHPExcel/IOFactory.php";
        $excel = new \PHPExcel();
        //Excel表格式,这里简略写了10列
        $column = array('A','B','C','D','E','F','G','H','I','J');
        $tableheader_count = count($tableheader);
        $end_column = $column[$tableheader_count];
        for ($i=0; $i < $tableheader_count; $i++) {
            $letter[] = $column[$i];
        }

        //设置列宽
        foreach ($letter as $lekey => $levalue) {
            $excel->getActiveSheet()->getColumnDimension($levalue)->setWidth(22);
        }

        //p($data);exit;//填充表头信息
        for($i = 0;$i < $tableheader_count;$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }

        ////填充表格信息
        $count = count($data);
        for ($i = 2;$i <= $count + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $k=>$v) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$v");
                $j++;
            }
        }
        //创建Excel输入对象
        $write = new \PHPExcel_Writer_Excel5($excel);
        header("Content-Type:application/vnd.ms-execl");
        header('Content-Disposition:attachment;filename="'.$filename.'.xls"');
        $write->save('php://output');
        return true;
    }
}