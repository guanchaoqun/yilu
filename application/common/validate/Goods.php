<?php


namespace app\common\validate;


use think\Validate;

class Goods extends Validate
{
    protected $auto = ['create_time' , 'update_time'];
    protected $rule = [
        'goods_name'   => 'require|max:150',

    ];

    protected $message = [
        'goods_name.require'     => '商品名称不能为空',
        'goods_name.max'         => '商品名称的长度不能超过150',

    ];

    protected $scene = [
        'add'      => ['goods_name'],
        'edit'     => ['goods_name'],
    ];
}