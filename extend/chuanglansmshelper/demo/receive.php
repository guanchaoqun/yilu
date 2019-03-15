<?php
header('Content-Type:text/html;charset=utf-8');

$receiver = isset($_GET['receiver'])?$_GET['receiver']:'';
$pswd = isset($_GET['pswd'])?$_GET['pswd']:'';
$msgid = isset($_GET['msgid'])?$_GET['msgid']:'';
$reportTime = isset($_GET['reportTime'])?$_GET['reportTime']:'';
$mobile = isset($_GET['mobile'])?$_GET['mobile']:'';
$status = isset($_GET['status'])?$_GET['status']:'';

//接收的数据保存起来即可