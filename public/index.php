<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

//header('Access-Control-Allow-Origin: *');
//header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");
//header('Access-Control-Allow-Methods: POST,GET');
// 定义应用目录
//header('Access-Control-Allow-Origin:*');
//header('Access-Control-Allow-Methods:HEAD,GET,POST,OPTIONS,PATCH,PUT,DELETE');
//header('Access-Control-Allow-Headers:Origin,X-Requested-With,Authorization,Content-Type,Accept,Z-Key');

define('APP_PATH', __DIR__ . '/../application/');
define('WEIXINPAY_PATH', __DIR__     . '/../weixincert/');
define('AppID','wx562c71c4fa52228f');
define('AppSecret','969df605d5eaafecff40484d7d9eda64');
define('Token','abcd');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
