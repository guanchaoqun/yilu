<?php


namespace app\common\validate;


use think\Validate;

class Member extends Validate
{
    protected $rule = [
//        'nickname'   => 'require|chsAlpha|max:50',
        'nickname'   => 'require|max:50',
        'phone'=>'/^1[34578]\d{9}$/',
    ];

    protected $message = [
        'nickname.require'     => '姓名不能为空',
//        'nickname.chsAlpha' => '姓名只能是汉字或字母',
        'nickname.max'         => '姓名的长度不能超过50',
        'phone'         => '手机号格式不正确',
    ];

    protected $scene = [
        'add'      => ['nickname', 'phone'],
        'edit'     => ['nickname', 'phone'],
    ];
}