<?php


namespace app\common\validate;


use think\Validate;

class ArtistClass extends Validate
{
    protected $rule = [
        'name'   => 'require|chsAlpha|max:50',
    ];

    protected $message = [
        'name.require'     => '姓名不能为空',
        'name.chsAlpha' => '姓名只能是汉字或字母',
        'name.max'         => '姓名的长度不能超过50',
    ];

    protected $scene = [
        'add'      => ['name'],
        'edit'     => ['name'],
    ];
}