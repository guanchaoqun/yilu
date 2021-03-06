<?php
header("Content-type:text/html; charset=UTF-8");

// 引用ChuanglanSmsApi类文件 
require_once 'ChuanglanSmsHelper/ChuanglanSmsApi.php';

//创建一个类
$clapi  = new ChuanglanSmsApi();

$code = mt_rand(100000,999999);

/**
* 调用短信方法  
 * 如果需要发送多个手机号码 请用英文逗号","隔开
 * 签名需在平台审核通过后 在短信内容前面添加
 */
$result = $clapi->sendSMS('15605153256', '【253云通讯】您好，您的验证码是'. $code);

//返回值处理返回数组
$result = $clapi->execResult($result);

//返回的状态码
$statusStr = array(
	'0' =>'发送成功',
    '101'=>'无此用户',
    '102'=>'密码错',
    '103'=>'提交过快',
    '104'=>'系统忙',
    '105'=>'敏感短信',
    '106'=>'消息长度错',
    '107'=>'错误的手机号码',
    '108'=>'手机号码个数错',
    '109'=>'无发送额度',
    '110'=>'不在发送时间内',
    '111'=>'超出该账户当月发送额度限制',
    '112'=>'无此产品',
    '113'=>'extno格式错',
    '115'=>'自动审核驳回',
    '116'=>'签名不合法，未带签名',
    '117'=>'IP地址认证错',
    '118'=>'用户没有相应的发送权限',
    '119'=>'用户已过期',
    '120'=>'内容不是白名单',
    '121'=>'必填参数。是否需要状态报告，取值true或false',
    '122'=>'5分钟内相同账号提交相同消息内容过多',
    '123'=>'发送类型错误(账号发送短信接口权限)',
    '124'=>'白模板匹配错误',
    '125'=>'驳回模板匹配错误',
    '128'=>'内容解码失败'
);
if(isset($result[1])){
	echo $statusStr[$result[1]];
}else{
	echo "发生未知错误";
}