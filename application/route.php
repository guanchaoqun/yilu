<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[index]'     => [
        'case$'            => ['index/case_list/index'],
        'case/:cid$'       => ['index/case_list/index', [], ['cid' => '\d+']],
        'case/:cid/:page$' => ['index/case_list/index', [], ['cid' => '\d+', 'page' => '\d+']],
        'caseinfo/:cid$'   => ['index/case_list/info', [], ['cid' => '\d+']],
        'active$'          => ['index/index/active'],
        'signup$'          => ['index/index/sginup'],
        'contactus$'       => ['index/index/contactus'],
    ],
];

