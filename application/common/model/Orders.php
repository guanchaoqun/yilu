<?php
namespace app\common\model;


use think\Model;

class Orders extends Model
{
    public function getStatusAttr($value)
    {
//        1-待付款 2--待发货 3--待收货 4--已完成 5--违约
        $status = [1=>'待付款',2=>'待发货',3=>'待收货',4=>'已完成',5=>'违约'];
        return $status[$value];
    }
}