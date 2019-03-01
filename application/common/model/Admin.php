<?php
/**
 *
 * Created by PhpStorm.
 * User: 星亿达
 * Date: 2018/3/14
 * Time: 16:27
 */

namespace app\common\model;


use think\Model;
use traits\model\SoftDelete;

class Admin extends Model
{
    use SoftDelete;
    //    开启自动写入
    protected $deleteTime = 'delete_time';
    //只读字段
    protected $readonly = ['username'];
    //自动写入
    protected $insert  = ['logins', 'last_time', 'last_ip'];

    protected $updateTime = false;

    protected function setLoginsAttr($value)
    {
        $logins=isset($value)?$value:0;
        return $logins;
    }
    protected function setLastTimeAttr()
    {
        return date("Y-m-d H:i:s");
    }
    protected function setLastIpAttr()
    {
        return request()->ip();
    }
}