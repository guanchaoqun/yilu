<?php


namespace app\common\model;
use think\Model;

class Goods extends Model
{
    protected $table = 'goods';
    protected $pk = 'id';
    public function cates()
    {
        return $this->belongsTo('Cates','cates_id','id');
    }

    public function artist()
    {
        return $this->belongsTo('Artist','artist_id','id');
    }
}