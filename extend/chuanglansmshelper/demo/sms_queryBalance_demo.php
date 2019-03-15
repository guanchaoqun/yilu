<?php
header("Content-type:text/html; charset=UTF-8");

// 引用ChuanglanSmsApi类文件 
require_once 'ChuanglanSmsHelper/ChuanglanSmsApi.php';
$clapi  = new ChuanglanSmsApi();
$result = $clapi->queryBalance();
$result = $clapi->execResult($result);

//返回值处理
if(isset($result[1])){
	switch($result[1]){
		case 0:
			echo "剩余{$result[2]}条";
			break;
		case 101:
			echo '无此用户';
			break;
		case 102:
			echo '密码错';
			break;
		case 103:
			echo '查询过快';
			break;
	}
}else{
	echo "查询失败";
}