<?php
/**
 *
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 17:51
 */

namespace app\common\model;


use think\Model;

class AuthRule extends Model
{


    public function getLevelTurnAttr($value, $data)
    {
        $turnArr = [1 => '模块', 2 => '功能', 3 => '操作'];

        return $turnArr[ $data['level'] ];
    }

    public function treeList($module = 'webcontroller', $status = '')
    {
        if ($module != '') {
            $where = [
                'module' => $module,
            ];
        }
        if ($status != '') {
            $where['status'] = $status;
        }
        $list      = $this->where($where)->order('sorts ASC,id ASC')->select();
        $treeClass = new \expand\Tree();
        $list      = $treeClass->create($list);

        return $list;
    }
}