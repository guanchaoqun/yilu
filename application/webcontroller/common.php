<?php
/**
 * Created by PhpStorm.
 * User: GUO
 * Date: 2017/12/31
 * Time: 22:50
 */

/**
 * 权限验证
 * @param        $rule
 * @param        $uid
 * @param string $relation
 * @param string $t
 * @param string $f
 * @return string
 */
function authcheck($rule, $uid, $relation='or', $t='', $f='noauth'){
    $auth = new \expand\Auth();
    if( $auth->check($rule, $uid, $type=1, $mode='url',$relation) ){
        $result = $t;
    }else{
        $result = $f;
    }

    return $result;
}