<?php
/**
 *
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/15
 * Time: 9:51
 */

namespace app\common\validate;


use think\Validate;

class AuthRule extends Validate
{
    protected $rule = [
        'pid'    => 'require|integer',
        'title'  => 'require',
        //'name'   => 'require|unique:auth_rule|/^[a-zA-Z0-9\/\-\_]+$/',
        'name'   => 'require|/^[a-zA-Z0-9\/\-\_]+$/',
        'level'  => 'require|in:1,2,3',
        'status' => 'require|in:0,1',
        'ismenu' => 'require|in:0,1',
        'sorts'  => 'require|integer|>=:1',
    ];

    protected $message = [
        'pid'          => '父级节点必须为数字整数',
        'title'        => '节点名称不能为空',
        'name.require' => '节点地址不能为空',
        //'name.unique'  => '节点地址已存在',
        'name'         => '节点地址必须为有效的URL地址',
        'level'        => '节点类型必须为数字整数（1,2,3）',
        'status'       => '状态必须为数字整数（0,1）',
        'ismenu'       => '是否菜单为数字整数（0,1）',
        'sorts'        => '排序必须为大于0数字整数',
    ];

    protected $scene = [
        'add'    => ['pid', 'title', 'name', 'level', 'status', 'ismenu', 'sorts'],
        'edit'   => ['pid', 'title', 'name', 'level', 'status', 'ismenu', 'sorts'],
        'status' => ['status'],
        'ismenu' => ['ismenu'],
        'title'  => ['title'],
        'name'   => ['name'],
    ];
}