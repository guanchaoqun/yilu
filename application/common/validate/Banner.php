<?php

namespace app\common\validate;

use think\Validate;

class Banner extends Validate
{
    protected $auto = ['create_time' , 'update_time'];
    protected $rule = [
        'banner_name'  => 'require|max:100',
        'banner_url'   => 'require|/[a-zA-Z0-9]+/',
        'banner_img'   => 'require',
        'start_time'   => 'require',
        'end_time'   => 'require',
    ];

    protected $message = [
        'banner_name.require'   => '名称不能为空',
        'banner_name.max'       => '名称的长度不能超过100',
        'banner_url.require'    => '链接不能为空',
        'banner_url'            => '请填写有效链接',
        'banner_img.require'    => '请上传图片',
        'start_time.require'    => '请选择开始时间',
        'end_time.require'      => '请选择结束时间',
    ];

    protected $scene = [
        'add'      => ['banner_name','banner_url','banner_img'],
        'edit'     => ['banner_name','banner_url','banner_img'],
    ];
}