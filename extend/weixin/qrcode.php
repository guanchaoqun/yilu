<?php

/**********************************************************\
 *                                                        *
 * msg.php                                         		  *
 *                                                        *
 * 微信公众平台  二维码  use php5.4                       *
 *                                                        *
 * LastModified: 2018-02-05                               *
 * Author: eric <765215770@qq.com>                   	  *
 *                                                        *
\**********************************************************/
    trait qrcode{

        /**
         * 创建ticket
         * User:Vernon
         * Date: 2018-02-05
         * @param:
         * @return:josn
         */

        public function createTicket(){
//            header("Content-type:text/html;charset=utf8");
            $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".access_token();
            $scene_str = get_vc(6,2);
            $postArr=array(
                //'expire_seconds' => 2592000,//30天
                'action_name'    =>"QR_LIMIT_STR_SCENE",//永久字符串
                'action_info'	=>array(
                    'scene'	=>array(
                        'scene_str'=>$scene_str
                    )
                ),
            );
            $postJson = json_encode($postArr);
            $res = http_curl($url,'post','json',$postJson);
            $res['scene_str'] = $scene_str;
            return $res;
        }

        public function temporaryQrcode(){
            $url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQEq8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyLWpEXzUzcGRlMWoxR3R3dmhxMXAAAgSdE3haAwQAjScA";
            echo "<img src = '".$url."'>";
        }
    }