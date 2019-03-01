<?php
/**
 *
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 16:30
 */

namespace app\common\model;


use think\Model;

class LoginLog extends Model
{
    public function Admin()
    {
        return $this->hasOne('Admin', 'admin_id', 'uid')->field('username, nick_name');
    }
}