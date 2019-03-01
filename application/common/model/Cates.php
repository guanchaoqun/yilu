<?php
namespace app\common\model;
use think\Model;

class Cates extends Model
{
    protected $table = 'cates';
    protected $pk = 'id';

    public function goods()
    {
        return $this-> hasMany('Goods','cates_id','id');
    }


}