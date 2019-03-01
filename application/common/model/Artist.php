<?php

namespace app\common\model;


use think\Model;

class Artist extends Model
{
    protected $table = 'artist';
    protected $pk = 'id';
        public function goods()
        {

            return $this->hasMany('Goods','artist_id','id');
        }
        public function artistClass()
        {
            return $this->belongsTo('artist_class','artist_class_id','id');
        }

}