<?php
/**
 *
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/15
 * Time: 9:40
 */

namespace app\common\validate;


use think\Validate;

class AuthGroup extends Validate
{
    protected $rule = [
        'title' => 'require',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'title' => '角色名称不能为空',
        'status' => '状态必须为数字整数（0,1）',
    ];
}