<?php

namespace app\api\controller;

use think\Controller;
use EasyWeChat\Factory;
use think\Request;

class Weixin extends Controller
{

    //用户首次配置开发环境
    public function echoStr(Request $request)
    {
//     开发者模式配置
//        $data = $request->param();
//        file_put_contents('111.log',$data."\r\n\r\n",8);
//        $token = Token;
//        $signature = $data["signature"];
//        $timestamp = $data["timestamp"];
//        $nonce = $data["nonce"];
//        $echostr = $data["echostr"];
//        $tmpArr = array($token, $timestamp, $nonce);
//        sort($tmpArr, SORT_STRING);
//        $tmpStr = implode($tmpArr);
//        $tmpStr  = sha1($tmpStr);
//        if ($tmpStr == $signature) {
//            echo $echostr;
//            exit();
//        }

        $postStr = file_get_contents("php://input");//访问请求的原始数据的只读流
        if (!empty($postStr)) {
//            file_put_contents('wx.log', $postStr . "\r\n\r\n", 8);
//            $this->logger("R ".$postStr);
            //考虑到了安全问题。
//                libxml_disable_entity_loader(true);
            //得到了数据之后，然后我们就是要解析微信服务器发送过来的xml数据包了
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $type = trim($postObj->MsgType);
            //返回的信息
            $fromruseropenid = trim($this->getUserInfo($postObj->FromUserName));
            //关注之后利用二次opendi，获取用户信息
            $userinfostr = json_decode($fromruseropenid, true)["nickname"];
             json_decode($fromruseropenid, true);
            switch ($type) {

                case "event":
                    $result = $this->receiveEvent($postObj, $userinfostr);
                    break;
                case "text":
                    $result= "";
//                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = "";
//                        $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = "";
//                        $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = "";
//                        $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = "";
//                        $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = "";
//                        $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: " . $type;
                    break;

            }
            echo $result;
        } else {
            echo "";
            exit;
        }

    }


    //接收事件消息
    private function receiveEvent($object, $userinfostr)
    {
        $content = "";
        switch ($object->Event) {
            case "subscribe":
//                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                $content = "时尚需要引领,尤其在中国。只买贵的不买对的的社会现状，符合久贫乍富的土鳖阶级装屄审美取向。" . "\n感谢您的关注:" . $userinfostr;
                break;
        }
        $result = $this->transmitText($object, $content);
        return $result;
    }

//    //接收文本消息
//    private function receiveText($object)
//    {
//        switch ($object->Content) {
//
//            case "你是":
//                $content = "一个不愿透露名字的机器人,略略略";
//                break;
//            case "你好":
//                $content = "你好,感谢您的关注";
//                break;
//            default:
////                $content = $object->Content;
//                $content = "";
//                break;
//        }
//        if (is_array($content)) {
//            if (isset($content[0]['PicUrl'])) {
//                $result = $this->transmitNews($object, $content);
//            } else if (isset($content['MusicUrl'])) {
//                $result = $this->transmitMusic($object, $content);
//            }
//        } else {
//            $result = $this->transmitText($object, $content);
//        }
//        return $result;
//    }


//    回复文本消息
    private function transmitText($object, $content)
    {
        $textTpl = "<xml><ToUserName>
                             <![CDATA[%s]]>
                        </ToUserName>
                        <FromUserName>
                             <![CDATA[%s]]>
                        </FromUserName>
                        <CreateTime>
                                %s
                        </CreateTime>
                        <MsgType>
                                <![CDATA[text]]>
                        </MsgType>
                        <Content>
                                <![CDATA[%s]]>
                        </Content>
                </xml>";

//        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }


    //创建菜单 OK 自定义菜单
    public function creatMenu()
    {
//        var_dump(self::getAccessToken());exit;
        //组装请求的url地址
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . access_token();

        $data = array(
            // button下的每一个元素
            "button" => array(
                //第一个一级菜单
                array('type' => 'view', "name" => "在线拍卖", 'url' => "http://www.yiluzongheng.com/api/wechat/snsapiUserinfo"),
                array('type' => 'view', "name" => "原创佳品", 'url' => "http://mp.weixin.qq.com/bizmall/mallshelf?id=&t=mall/list&biz=MzI2NDkwMzIxNw==&shelf_id=2&showwxpaytitle=1#wechat_redirect"),
                array('type' => 'view', "name" => "文人雅致", 'url' => "http://mp.weixin.qq.com/bizmall/mallshelf?id=&t=mall/list&biz=MzI2NDkwMzIxNw==&shelf_id=1&showwxpaytitle=1#wechat_redirect"),

//                array('type'=>'miniprogram','name'=>'夏令营','url'=>'http://mp.weixin.qq.com','appid'=>'wxb3dddde5704f9c5','pagepath'=>'pages/baoming/baoming'),

//                array('type'=>'miniprogram','name'=>'教育','url'=>'http://mp.weixin.qq.com','appid'=>'wxbddd1d1ce5704f9c5','pagepath'=>'pages/index/index'),
            )
        );
        //    将数据转换为json格式
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
//        $curl= new Tool();
        $result = http_curl($url, 'post', 'json', $data);
        dump($result);
    }


//获取自定义菜单
    public function getMenu()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . access_token();
//        $curl = new Tool();
        $res = http_curl($url);
        var_dump($res);
    }

// 删除自定义菜单
    public function delMenu()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.access_token();
//        $curl = new Tool();
        $res = http_curl($url);
//        dump($res);
    }
//关注之后返回用户的信息
    public function getUserInfo($openid)
    {
        //获取token
        $token = access_Token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$openid&lang=zh_CN";
        $data = file_get_contents($url);
        $user = json_decode($data, true);
        $unionid = $user["openid"];
        $urll = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$token&openid=$unionid&lang=zh_CN";
        //获取用户信息
        $userinfo = file_get_contents($urll);
        return $userinfo;
    }



//    /**
//     * 发送模版消息
//     *   /api/weixin/sendTemplateMessage
//     */
//
//    public function sendTemplateMessage(Request $request)
//    {
//
//        $res = $request->param();
////        $id = $res['id'];
////        dump($id);
//        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . access_token();
//
////    接受模板消息的用户openid
//        $openid = 'oMXiawvW7cvZPpTBDxGTOPbwzHsQ';
////        $openid = 'oMXiawqeKDHfc4xX1ynOymSmlo9c';
//        //模板消息id
//        //发货通知
//        $template_id = 'QiqHMKWE7PoHhknLbic84cGn8g0fmAJz-7ITkuPqf7E';
//        //设置模板消息
//        $array = array();
//        //设置接受消息用户的openid
//        $array['touser'] = $openid;
//        //设置模板消息id
//        $array['template_id'] = $template_id;
//        $array['url'] = 'http://www.yiluzongheng.com/home/my';
//        //设置模板消息    $data = array();
//        $data['first'] = array();
//        $data['first']['value'] = '您好,您的订单已发货';
//        $data['first']['color'] = '#173177';
//        $data['keyword1'] = array();
//        $data['keyword1']['value'] = '顺风快递';
//        $data['keyword1']['color'] = '#173177';
//        $data['keyword2'] = array();
//        $data['keyword2']['value'] = '1231321';
//        $data['keyword2']['color'] = '#173177';
//        $data['keyword3'] = array();
//        $data['keyword3']['value'] = 'goodsname';
//        $data['keyword3']['color'] = '#173177';
//        $data['keyword4'] = array();
//        $data['keyword4']['value'] = '数量1';
//        $data['keyword4']['color'] = '#173177';
//        $data['remark'] = array();
//        $data['remark']['value'] = '请注意查收';
//        $data['remark']['color'] = '#173177';
//        $array['data'] = $data;
//        //调用公共方法curl_post，发送模板消息
//        curl_post($array, $url);
//    }


}

