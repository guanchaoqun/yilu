<?php


// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------



// 应用公共文件


/**
 * 打印函数
 * @param $var 打印内容
 */
function p($var){
    if($var===false){
        var_dump($var);
    }else{
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }
}

/**
 * 邀请码
 * @param $user_id
 * @return string
 */

function createCode($user_id) {
    static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
    $num = $user_id;
    $code = '';
    while ( $num > 0) {
        $mod = $num % 35;
        $num = ($num - $mod) / 35;
        $code = $source_string[$mod].$code;
    }
    if(empty($code[3]))
        $code = str_pad($code,4,'0',STR_PAD_LEFT);
    return $code;
}

/**
 * 解码函数
 * @param $code
 * @return bool|int
 */
function decode($code) {
    static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
    if (strrpos($code, '0') !== false)
        $code = substr($code, strrpos($code, '0')+1);
    $len = strlen($code);
    $code = strrev($code);
    $num = 0;
    for ($i=0; $i < $len; $i++) {
        $num += strpos($source_string, $code[$i]) * pow(35, $i);
    }
    return $num;
}

/**
 *  API返回信息格式函数 ；失败：code=110，成功：code=200
 * @param string $code
 * @param string $message
 * @param array $data
 */
function apiResponse($code = '110', $message = '',$data = array()){
    header('Access-Control-Allow-Origin: *');
    header('Content-Type:application/json; charset=utf-8');
    $result = array(
        'code'=>$code,
        'message'=>$message,
        'data'=>$data,
        // 'nums'=>''.$nums
    );
    die(json_encode($result,JSON_UNESCAPED_UNICODE));
//    die(json_encode($result));

}

/**
 *
 * 根据id获取任意字段值
 * @return [string] [状态]
 */
function getName($model='',$field='',$id=0){
    if($id && $model && $field){
        return db($model)->where("id={$id}")->value($field).'';
    }else{
        return '';
    }
}
/**
 *
 * 根据id获取任意字段值
 * @return [string] [状态]
 */
function getAddress($model='',$field='',$id=0){
    if($id && $model && $field){
        return db($model)->where("areaid={$id}")->value($field).'';
    }else{
        return '';
    }
}
/**
 * 获取','隔开的数据的第一个元素
 * @param $string ','隔开字符串
 * @return json
 */
function getFirst($string){
    $string = explode(',',$string);
    return reset($string);
}
/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param string $sortby 排序类型 （asc正向排序 desc逆向排序 nat自然排序）
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list))
    {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
        {
            $refer[$i] = &$data[$field];
        }
        switch ($sortby)
        {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc': // 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
        {
            $resultSet[] = &$list[$key];
        }
        return $resultSet;
    }
    return false;
}

/**
 * 验证码
 * @param int $flag 0数字字符混合 1字符 2数字
 * @param int $num 验证标识的个数
 * @return string
 */
function get_vc($num = 0, $flag = 0) {

    /**获取验证标识**/
    $arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',1,2,3,4,5,6,7,8,9,0);
    $vc  = '';
    switch($flag) {
        case 0 : $s = 0;  $e = 61; break;
        case 1 : $s = 0;  $e = 51; break;
        case 2 : $s = 52; $e = 61; break;
    }

    for($i = 0; $i < $num; $i++) {
        $index = rand($s, $e);
        $vc   .= $arr[$index];
    }
    return $vc;
}

function wxinit(){
    $wx = new \weixin\Wx();
    $wx->AppID=AppID;
    $wx->AppSecret=AppSecret;
    $wx->Token=Token;
    $wx->access_token=access_token();
//    p(access_token());
    return $wx;
}
/**
 * curl请求
 */
function http_curl($url, $type = 'get', $res = 'json', $arr = ''){
    $cl = curl_init();
    curl_setopt($cl, CURLOPT_URL, $url);
    curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
    if($type == 'post'){
        curl_setopt($cl, CURLOPT_POST, 1);
        curl_setopt($cl, CURLOPT_POSTFIELDS, $arr);
    }
    $output = curl_exec($cl);
    curl_close($cl);
    return json_decode($output, true);
     if($res == 'json'){
         if( curl_error($cl)){
             return curl_error($cl);
         }else{
             return json_decode($output, true);
         }
     }
}

//curl使用post方式请求url,参数为$arr是post方式传送的数据,为数组类型,$url为需要请求的url
//公众号自定义 菜单
function curl_post($arr,$url){
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($arr));
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据

//    print_r($data);
    return $data;
}

/**
 * @param $arr
 * @param $url
 *   超价提醒 模版 消息
 */
function curl_posts($arr,$url){
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, 1);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($arr));
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);


}


//function http_curl($url,$data =array(),$method ="get",$returnType ="json")
//{
//    //1.开启会话
//    $ch = curl_init();
//    //2.设置参数
//
//    curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
//    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
//    if($method!="get"){
//        curl_setopt($ch,CURLOPT_POST,TRUE);
//        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
//    }
//    curl_setopt($ch,CURLOPT_URL,$url);
//    //执行会话
//    $json = curl_exec($ch);
//    curl_close($ch);
//    if($returnType == "json"){
//        return json_decode($json,true);
//    }
//    return $json;
//}


/**
 * 获取access_token
 * @return access_token
 */
function access_token(){
//    $access_token = db('access_token')->whereTime('update_time','-2 hours')->value('access_token');
    $access_token = db('access_token')->whereTime('update_time','-2 hours')->value('access_token');
    if($access_token){
        return $access_token;
    }else{
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".AppID."&secret=".AppSecret;
        $access_token =  http_curl($url,'get');
        $save['update_time'] = time();
        $save['access_token'] = $access_token['access_token'];
        db('access_token')->where('id',1)->update($save);
        return $access_token;
    }
}

//获取用户基本信息
function getUser($data){
    $access_token = access_token();
    $openid=$data['FromUserName'];
    $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
    $res=http_curl($url,'get');
    return $res;
}


/**
 * 文件下载
 * @param string
 * @return json
 */
function download($path,$name){
    //1.获取要下载图片的文件名和路径
    $file = $path.$name;
    //2.重设响应类型var_dump(getimagesize($file));exit;
    $info = getimagesize($file);
    header("content-type:".$info['mime']);
    //3.执行下载的文件名，设定配置
    header("content-disposition:attachment;filename=".$name);
    //4.指定文件的大小
    header("content-length:".filesize($file));
    //5.读取文件内容 或者 readfile($file);
    echo file_get_contents($file);
}

// 1. 生成原始的二维码(生成图片文件)
function scerweima($value='',$name=''){

    $errorCorrectionLevel = 'L';    //容错级别
    $matrixPointSize = 5;           //生成图片大小
    $dir = './usersqcode/'.date('Y-m-d',time());
    if(!is_dir($dir)){
        mkdir(iconv('UTF-8','GBK',$dir),0777,true);
    }
    //生成二维码图片
    $filename = './usersqcode/'.date('Y-m-d',time()).'/'.$name;
    $object = new \expand\QRcode();
    $object->png($value,$filename , $errorCorrectionLevel, $matrixPointSize, 2);
    return $filename;
}

/**
 * 将xml转为array
 * @param $xml
 * @return mixed
 */
function xmlToArray($xml){
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}
/**

 * excel表格导出

 * @param string $fileName 文件名称

 * @param array $headArr 表头名称

 * @param array $data 要导出的数据

 * @author static7

 */

function excelExport($fileName = '', $headArr = [], $data = []) {
    import('phpexcel.PHPExcel', EXTEND_PATH);
    $fileName .= "_" . date("Y_m_d") . ".xls";

    $objPHPExcel = new \PHPExcel();

    $objPHPExcel->getProperties();

    $key = ord("A"); // 设置表头

    foreach ($headArr as $v) {

        $colum = chr($key);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

        $key += 1;

    }

    $column = 2;

    $objActSheet = $objPHPExcel->getActiveSheet();

    foreach ($data as $key => $rows) { // 行写入
        $span = ord("A");
        foreach ($rows as $keyName => $value) { // 列写入
            $objActSheet->setCellValue(chr($span) . $column, $value);
            $span++;
        }
        $column++;
    }

    $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表

    $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

    header('Content-Type: application/vnd.ms-excel');

    header("Content-Disposition: attachment;filename='$fileName'");

    header('Cache-Control: max-age=0');

    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $objWriter->save('php://output'); // 文件通过浏览器下载

    exit();

}

/**
 * 验证手机号是否正确
 * @author honfei
 * @param number $mobile
 */
function isMobile($mobile) {

    if (!is_numeric($mobile)) {

        return false;
    }
    return preg_match('/^(1[34578]|20)\d{9}$/', $mobile) ? true : false;
}

/**
 * 微信地图
 * @param string
 * @return json
 */
function mapConfig($parameter){
    if(!cache('?jsapi_ticket')){
        $jsapi_ticket = json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.access_token().'&type=jsapi'),true);
        cache('jsapi_ticket',$jsapi_ticket['ticket'],7000);
    }else{
        $jsapi_ticket['ticket'] =  cache('jsapi_ticket');
    }

    $config['time'] = time();
    $config['openid'] = config('weixin_golf.AppID');
    $config['noncestr'] = 'DRTYUIJHJKhhjj';
    $config['signature'] =  sha1('jsapi_ticket='.$jsapi_ticket['ticket'].'&noncestr='.$config['noncestr'].'&timestamp='.$config['time'].'&url=http://'.$_SERVER['HTTP_HOST'].'/'.$parameter);
    return $config;


}

/**
 * 获取汉字首字母
 */
function _getFirstCharter($str){
    // if(emptyempty($str)){return '';}
    $fchar=ord($str{0});
    if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
    $s1=iconv('UTF-8','gb2312',$str);
    $s2=iconv('gb2312','UTF-8',$s1);
    $s=$s2==$str?$s1:$str;
    $asc=ord($s{0})*256+ord($s{1})-65536;
    if($asc>=-20319&&$asc<=-20284) return 'A';
    if($asc>=-20283&&$asc<=-19776) return 'B';
    if($asc>=-19775&&$asc<=-19219) return 'C';
    if($asc>=-19218&&$asc<=-18711) return 'D';
    if($asc>=-18710&&$asc<=-18527) return 'E';
    if($asc>=-18526&&$asc<=-18240) return 'F';
    if($asc>=-18239&&$asc<=-17923) return 'G';
    if($asc>=-17922&&$asc<=-17418) return 'H';
    if($asc>=-17417&&$asc<=-16475) return 'J';
    if($asc>=-16474&&$asc<=-16213) return 'K';
    if($asc>=-16212&&$asc<=-15641) return 'L';
    if($asc>=-15640&&$asc<=-15166) return 'M';
    if($asc>=-15165&&$asc<=-14923) return 'N';
    if($asc>=-14922&&$asc<=-14915) return 'O';
    if($asc>=-14914&&$asc<=-14631) return 'P';
    if($asc>=-14630&&$asc<=-14150) return 'Q';
    if($asc>=-14149&&$asc<=-14091) return 'R';
    if($asc>=-14090&&$asc<=-13319) return 'S';
    if($asc>=-13318&&$asc<=-12839) return 'T';
    if($asc>=-12838&&$asc<=-12557) return 'W';
    if($asc>=-12556&&$asc<=-11848) return 'X';
    if($asc>=-11847&&$asc<=-11056) return 'Y';
    if($asc>=-11055&&$asc<=-10247) return 'Z';
    return null;
}

function arrayToXml($arr){
    $xml = "<root>";
    foreach ($arr as $key=>$val){
        if(is_array($val)){
            $xml.="<".$key.">".arrayToXml($val)."</".$key.">";
        }else{
            $xml.="<".$key.">".$val."</".$key.">";
        }
    }
    $xml.="</root>";
    return $xml;
}

// 步骤1.设置appid和appsecret
//$appid = 'wxd75a2b20d3a54752';
//$appsecret = '9b32270f32874ea7a7427f88ff770777';


// 步骤2.生成签名的随机串
 function nonceStr($length)
{
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    //62个字符
    $strlen = 62;
    while ($length > $strlen) {
        $str .= $str;
        $strlen += 62;
    }
    $str = str_shuffle($str);
    return substr($str, 0, $length);
}
// 步骤3.获取access_token
//    access_token();

  function http_get($url)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

// 步骤4.获取ticket
//
//$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$access_token";
//$res = json_decode ( http_get ( $url ) );
//$ticket = $res->ticket;


//获取微信签名所需的 ticket
 function getTicket(){
//    $token = $this->getAccessToken();
    $token=access_token();
    $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
//        $tmp = $this->http_get($url); //json格式
    $res = json_decode(http_get($url));//json格式
//        $obj = json_decode($tmp);

    return $res->ticket;

}

// 步骤5.生成wx.config需要的参数
 function getWxConfig()
{
    $timestamp=time();
    $nonceStr= nonceStr(16);   //获取签名随机串
    $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; //获取当前访问的URL

    $jsapiTicket=getTicket();
//        $ws = getWxConfig( $ticket,$surl,time(),nonceStr(16) );

    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
//        halt($string);
    $signature = sha1 ( $string );
//        halt($signature);
    $WxConfig["appId"] = AppID;
    $WxConfig["nonceStr"] = $nonceStr;
    $WxConfig["timestamp"] = $timestamp;
    $WxConfig["url"] = $url;
    $WxConfig["signature"] = $signature;
    $WxConfig["rawString"] = $string;

//        apiResponse('200','成功',array($WxConfig));
    return $WxConfig;
}



