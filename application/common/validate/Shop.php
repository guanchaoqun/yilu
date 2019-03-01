<?php


namespace app\common\validate;


use think\Validate;

class Shop extends Validate
{
    protected $rule = [
        'shopname'   => 'require|chsAlpha|max:50',
        'art_id'=>'require'
    ];

    protected $message = [
        'shopname.require'  => '名称不能为空',
        'shopname.chsAlpha' => '名称只能是汉字或字母',
        'shopname.max'     => '名称的长度不能超过50',
        'art_id.require'   => '商铺归属者不能为空'
    ];

    protected $scene = [
        'add'      => ['shopname'],
        'edit'     => ['shopname'],
        'add'      => ['art_id'],
        'edit'      => ['art_id'],
    ];
}