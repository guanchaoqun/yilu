<?php
namespace weixin;
/**********************************************************\
 * Wx.php  入口类                                         *
 * User Vernon                                            *
 * 微信公众平台   use php5.4                               *
 * LastModified: 2018-1-9                                 *
 * Author: eric <765215770@qq.com>                        *
 *                                                        *
\**********************************************************/
	if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');
	require_once('msg.php');
	require_once('pay.php');
	require_once('user.php');
	require_once('qrcode.php');
	class Wx{
		use \msg,\pay,\user,\qrcode;
		public $AppID="";  //应用ID
		public $EncodingAESKey=""; //消息加解密钥
		public $Token="";  //会话令牌
		public $access_token="";
		public $AppSecret="";
		public $account="";
		public $jsapiTicket="";
		public $mch_id="";
		public $apiKey="";
		public $original_id="";
	}
?>