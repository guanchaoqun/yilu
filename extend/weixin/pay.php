<?php
/**********************************************************\
 *                                                        *
 * pay.php                                                *
 * User Vernon                                            *
 * 微信公众平台   use php5.4                               *
 * 微信支付（公众号支付）                                   *
 * LastModified: 2018-1-9                                 *
 * Author: eric <765215770@qq.com>                        *
 *                                                        *
 * \**********************************************************/

ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);




trait pay
{
    /**
     * 微信支付
     * @param  string $openId openid
     * @param  string $goods 商品名称 商品描述
     * @param  string $attach 附加参数,我们可以选择传递一个参数,比如订单ID
     * @param  string $order_sn 订单号
     * @param  string $total_fee 金额
     */
    function wxpay($data)
    {
        if(!empty($data)) {


            require_once('WxPay/WxPay.JsApiPay.php');
            $tools = new JsApiPay();
            $input = new WxPayUnifiedOrder();
            $input->SetBody($data['goods']);           //商品名称 商品描述  这个值是有长度限制的string(128位)
            //要写成一个固定值 ,不能用商品名称
            //例如 腾讯充值中心--QQ会员充值
            $input->SetAttach($data['attach']);                  //附加参数,可填可不填,填写的话,里边字符串不能出现空格
            $input->SetOut_trade_no($data['order_sn']);          //订单号
            $input->SetTotal_fee($data['total_fee']);            //支付金额,单位:分
            $input->SetTime_start(date("YmdHis"));       //支付发起时间
            $input->SetTime_expire(date("YmdHis", time() + 600));//支付超时
            $input->SetGoods_tag("test3");    //订单优惠标记
            $input->SetNotify_url("http://www.yiluzongheng.com/api/wxpay/notify");//支付回调验证地址
            $input->SetTrade_type("JSAPI");              //支付类型
            $input->SetOpenid($data['openid']);         //用户openID

            $order = WxPayApi::unifiedOrder($input);    //统一下单
//        halt($order);
            $jsApiParameters = $tools->GetJsApiParameters($order);

            return $jsApiParameters;
        }else{
            return '参数错误';
        }
    }

    /**
     * 流程：
     * 1、组装包含支付信息的url，生成二维码
     * 2、用户扫描二维码，进行支付
     * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
     * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
     * 5、支付完成之后，微信服务器会通知支付成功
     * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
     */
    //模式二
    /**
     * 流程：
     * 1、调用统一下单，取得code_url，生成二维码
     * 2、用户扫描二维码，进行支付
     * 3、支付完成之后，微信服务器会通知支付成功
     * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
     */
    function saoma($data)
    {
        require_once('WxPay/WxPay.NativePay.php');
        if(!empty($data)) {
            $notify = new NativePay();
//        $url1 = $notify->GetPrePayUrl("123456789");

            $input = new WxPayUnifiedOrder();
            $input->SetBody($data['goods_name']);//商品名称 商品描述  这个值是有长度限制的string(128位)
            //要写成一个固定值 ,不能用商品名称
            //例如 腾讯充值中心--QQ会员充值
            $input->SetAttach($data['attach']);   //附加参数,可填可不填,填写的话,里边字符串不能出现空格
            $input->SetOut_trade_no($data['order_sn']);   //订单号
            $input->SetTotal_fee($data['total_fee']);   //支付金额,单位:分
            $input->SetTime_start(date("YmdHis"));   //支付发起时间
            $input->SetTime_expire(date("YmdHis", time() + 600));   //支付超时
//        $input->SetGoods_tag("test");      //订单优惠标记
            $input->SetNotify_url("http://www.yiluzongheng.com/api/wxpay/notify"); //通知地址
            $input->SetTrade_type("NATIVE");     //支付类型 扫码支付
            $input->SetProduct_id($data['order_sn']);   //商品ID
            $result = $notify->GetPayUrl($input);
            $url2 = $result["code_url"];
            return $url2;
        }else{
            return '参数错误';
        }
    }

    /**
     * 微信退款
     * @param  string $order_id 订单ID
     * @return 成功时返回(array类型)，其他抛异常
     */
    function wxRefund($data)
    {
        //我的SDK放在项目根目录下的Api目录下
        require_once('WxPay/WxPay.JsApiPay.php');
        //查询订单,根据订单里边的数据进行退款
        //$order = M('order')->where(array('id'=>$order_id,'is_refund'=>2,'order_status'=>1))->find();
        $merchid = WxPayConfig::MCHID;

//        if(!$order) return false;
        $input = new WxPayRefund();
        $input->SetOut_trade_no($data['order_sn']);            //自己的订单号
        //$input->SetTransaction_id($data['transaction_id']);  	//微信官方生成的订单流水号，在支付成功中有返回
        $input->SetOut_refund_no(get_vc(6, 2) . time());            //退款单号
//        $input->SetTotal_fee($data['bond']);            //订单标价金额，单位为分
        $input->SetTotal_fee($data['bond']*100);			//订单标价金额，单位为分
//        $input->SetRefund_fee($data['bond']);            //退款总金额，订单总金额，单位为分，只能为整数
        $input->SetRefund_fee($data['bond']*100);			//退款总金额，订单总金额，单位为分，只能为整数
        $input->SetOp_user_id($merchid);

        $result = WxPayApi::refund($input);    //退款操作

        // 这句file_put_contents是用来查看服务器返回的退款结果 测试完可以删除了
//        file_put_contents(APP_ROOT.'/Api/wxpay/logs/log3.txt',arrayToXml($result),FILE_APPEND);
        return $result;
    }

}