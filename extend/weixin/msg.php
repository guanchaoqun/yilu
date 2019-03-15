<?php
/**********************************************************\
 *                                                        *
 * msg.php                                         		  *
 *                                                        *
 * 微信公众平台  消息管理  use php5.4                     *
 *                                                        *
 * LastModified: 2016-1-6                             	  *
 * Author: eric <2660113514@qq.com>                   	  *
 *                                                        *
\**********************************************************/
trait msg{
    //获取用户信息
    public  function receive(){
        $postStr =  file_get_contents('php://input');

        if (!empty($postStr)){
            $data = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $data;
        }else {
            //get 方式传值过来
            $echoStr = $_GET["echostr"];
            header('Content-type: text/plain');
            if(strlen($echoStr)>0){
                if($this->checkSignature()){
                    echo $echoStr;
                    exit;
                }
            }
        }
    }

    //验证微信开发者模式接入是否成功
    private function checkSignature()
    {
        //signature 是微信传过来的 类似于签名的东西
        $signature = $_GET["signature"];

        //微信发过来的东西
        $timestamp = $_GET["timestamp"];
        //微信传过来的值  什么用我不知道...
        $nonce     = $_GET["nonce"];
        //定义你在微信公众号开发者模式里面定义的token
        $token  = "golf";
        //三个变量 按照字典排序 形成一个数组
        $tmpArr = array(
            $token,
            $timestamp,
            $nonce
        );
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        //哈希加密  在laravel里面是Hash::
        $tmpStr = sha1($tmpStr);
        //按照微信的套路 给你一个signature没用是不可能的 这里就用得上了
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }// checkSignature end

    //回复图文消息
    public function sendMultipleMsg($openId,$bussId,$data){
        $postXml="<xml><ToUserName>onJTNs2Ku_MlQgJ06nRsCVf08pgg</ToUserName><FromUserName>gh_793fa88d553a</FromUserName><CreateTime>1452160846</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>1</ArticleCount><Articles><item>
						<Title><![CDATA[fdsafdsa]]></Title> 
						<Description><![CDATA[fdsafdsafs]]></Description>
						<PicUrl><![CDATA[http://mp.weixin.qq.com/wiki/static/assets/dc5de672083b2ec495408b00b96c9aab.png]]></PicUrl>
						<Url><![CDATA[http://dcl.diancai.la/weixin/mem]]></Url>
					</item></Articles></xml>";
        echo $postXml;
    }
    //发送文本消息
    public function sendTextMsg($arr){
        $textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[text]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>
					</xml>";
        $retext=sprintf($textTpl,$arr['FromUserName'],$arr['ToUserName'],time(),$arr['keyword']);
        echo $retext;
    }
    //图文回复
    public function sendImgQrcode($arr){
        $ToUserName=$arr['FromUserName'];
        $FromUserName=$arr['ToUserName'];
        $CreateTime=time();
        $MsgType='news';
        $Articles=1;
        $Title=$arr['title'];
        $Description=$arr['description'];
        $PicUrl="http://dcl.diancai.la/man/Public/img/scanLife/menpiao.jpg";
        $Url='http://dcl.diancai.la/weixin/ticket?name='.$arr['FromUserName'];
        $template="<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[%s]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
<item>
<Title><![CDATA[%s]]></Title>
<Description><![CDATA[%s]]></Description>
<PicUrl><![CDATA[%s]]></PicUrl>
<Url><![CDATA[%s]]></Url>
</item>
</Articles>
</xml>";
        $info=sprintf($template,$ToUserName,$FromUserName,$CreateTime,$MsgType,$Articles,$Title,$Description,$PicUrl,$Url);
        echo $info;
    }
    //图片回复
    public function sendImgMsg($arr){
        $ToUserName=$arr['FromUserName'];
        $FromUserName=$arr['ToUserName'];
        $CreateTime=time();
        $MediaId = $arr['media_id'];
        $template="<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[%s]]></MediaId>
</Image>
	</xml>";
        $info = sprintf($template,$ToUserName,$FromUserName,$CreateTime,$MediaId);
        echo $info;
    }
}