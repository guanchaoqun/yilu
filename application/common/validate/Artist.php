<?php


namespace app\common\validate;


use think\Validate;

class Artist extends Validate
{
    protected $rule = [
//        'name'   => 'require|chsAlpha|max:50',
        'name'   => 'require|max:100',
        'phone'=>'/^1[34578]\d{9}$/',
    ];

    protected $message = [
        'name.require'     => '姓名不能为空',
        //'name.chsAlpha' => '姓名只能是汉字或字母',
        'name.max'         => '姓名的长度不能超过50',
        'phone'         => '手机号格式不正确',
    ];

    protected $scene = [
        'add'      => ['name', 'phone'],
        'edit'     => ['name', 'phone'],
    ];
}