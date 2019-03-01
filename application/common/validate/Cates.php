<?php


namespace app\common\validate;


use think\Validate;

class Cates extends Validate
{
    protected $rule = [
        'cname'   => 'require|chsAlpha|max:50',
    ];

    protected $message = [
        'cname.require'     => '名称不能为空',
        'cname.chsAlpha'    => '名称只能是汉字或字母',
        'cname.max'         => '名称的长度不能超过50',
    ];

    protected $scene = [
        'add'      => ['cname'],
        'edit'     => ['cname'],
    ];
}