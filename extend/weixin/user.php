<?php
/**********************************************************\
 *                                                        *
 * pay.php                                                *
 * User Vernon                                            *
 * 微信公众平台   use php5.4                              *
 * 处理用户授权相关内容                                   *
 * LastModified: 2018-1-9                                 *
 * Author: eric <765215770@qq.com>                        *
 *                                                        *
\**********************************************************/
trait user{

	/**
	 * User:Vernon
	 * Date: 2018-01-09
	 * @param:$openid 用户 openid
	 * @return: json
	 */
	function getUser($openid){
		$url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".access_token()."&openid=".$openid."&lang=zh_CN";
		$res=http_curl($url,'get');
		return $res;
	}

	/**
	 * 网页普通授权
	 * User:Vernon
	 * Date: 2018-01-09
	 * @param:$redirect_uri 回调地址
	 * @return:
	 */
	function snsapiBase($redirect_uri){
//		$redirect_uri=urlEncode('http://www.rrffq.com/index.php/Home/Index/index');
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->AppID."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_base&state=789#wechat_redirect";
		header('location:'.$url);
	}

	/**
	 * 网页高级授权
	 * User:Vernon
	 * Date: 2018-01-09
	 * @param:$redirect_uri 回调地址
	 * @return:
	 */
	function snsapiUserinfo($redirect_uri){
//		$redirect_uri=urlEncode('http://www.rrffq.com/index.php/Home/Index/index');
		//高级授权url
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->AppID."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_userinfo&state=999#wechat_redirect";
		header('location:'.$url);
             // https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
	}

	/**
	 * 获取用户的openid
	 * User:Vernon
	 * Date: 2018-01-09
	 * @param: $_GET 微信get提交的参数
	 * @return: json
	 */
	function getOpenid($res){
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->AppID."&secret=".$this->AppSecret."&code=".$res['code']."&grant_type=authorization_code";
		$openid = http_curl($url,'get');
		return $openid;
	}

	/**
	 * 授权后获取用户基本信息
	 * User:Vernon
	 * Date: 2018-01-26
	 * @param:
	 * @return:
	 */
	function getUserInfo($res){
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$res['access_token']."&openid=".$res['openid']."&lang=zh_CN";
		$user_info = http_curl($url,'get');
		return $user_info;
	}
}