<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:68:"/data/wwwroot/aaa/public/../application/api/view/goodspay/index.html";i:1537411781;}*/ ?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>支付</title>
    <script type="text/javascript" src="/home/js/one.js"></script>
    <link rel="stylesheet" type="text/css" href="/home/css/daifukuan.css"/>
    <style>
        .one{
            width:435px;
            height:70px;
            word-break: break-all;
            text-overflow: ellipsis;
            display: -webkit-box; /** 对象作为伸缩盒子模型显示 **/
            -webkit-box-orient: vertical; /** 设置或检索伸缩盒对象的子元素的排列方式 **/
            -webkit-line-clamp: 2; /** 显示的行数 **/
            overflow: hidden;
        }
        .second-p2{
            font-size: 20px;
            padding: 1px 0 53px;
            height: 30px;
            line-height: 80px;
        }
    </style>
</head>
<body>
<br/>
<!--<span><span style="color:#000; font-size:30px;"><?php echo $res['goods_name']; ?></span></span><br/>
<font color="#000"><b>订单金额为<span style="color:#f00;font-size:40px;"><?php echo $res['end_price']+$res['postage']; ?></span>元</b></font><br/><br/>
<font color="#000"><b>商品金额<span style="color:#f00;font-size:40px;"><?php echo $res['end_price']; ?></span>元</b></font><br/><br/>
<font color="#000"><b>邮费<span style="color:#f00;font-size:40px;"><?php echo $res['postage']; ?></span>元</b></font><br/><br/>

<font color="#9ACD32"><b><span style="color:#f00;font-size:50px;margin-left:40%;">1分</span>钱也是爱</b></font><br/><br/>-->
<div class="box">
    <div v-for="(item,index) in daiFukuan">
        <div class="header">
            <div class="header-1">
                <p class="header-p1">等待买家付款</p>
                <p class="header-p2" id="sheng">剩余 <span id="timer"></span>自动关闭订单</p>
            </div>
            <div class="weizhi">
                <div class="weizhi-xq">
                    <p class="xm">
                        <span><?php echo $res['name']; ?></span>
                        <span><?php echo $res['phone']; ?></span>
                    </p>
                    <p class="dz">
                        <?php echo $res['address']; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="second">
            <div class="second-1" @click="location.href = 'focusStore.html?id='+item.s_id">
                <p><?php echo $res['shop']; ?></p>
            </div>
            <div class="second-2" @click="location.href = 'ongoing.html?id='+item.g_id">
                <div class="second-3">
                    <div class="second-2-left">
                        <img src="<?php echo $res['cover_plan']; ?>">
                    </div>
                    <div class="second-2-right">
                        <p class="second-p1 one"><?php echo $res['goods_name']; ?></p>
                        <p class="second-p2">竞拍价：<span>￥<?php echo number_format($res['end_price'],2); ?></span></p>
                        <p class="second-p3">保证金：￥<?php echo $goods['bond']; ?></p>
                    </div>
            </div>
        </div>
        <div class="three">
            <div class="three-1">
                <p class="three-p1">成交价格<span>￥<?php echo number_format($res['end_price'],2); ?> </span></p>
                <p class="three-p2">保证金额<span>-￥<?php echo $goods['bond']; ?> </span></p>
                <p class="three-p3">运费<span>￥<?php echo number_format($res['postage'],2); ?> </span></p>
                <p class="three-p4">实付金额<span>￥<?php echo number_format($res['end_price']+$res['postage'],2); ?> </span></p>
            </div>
        </div>
        <div class="four">
            <div class="four-1">
                <p>订单编号<span><?php echo $res['order_sn']; ?></span></p>
                <p style="display:none">成交时间<span id="cheng"><?php echo $res['create_time']; ?></span></p>
                <p>成交时间<span id="jiao"></span></p>
            </div>
        </div>
        <div class="five">
            <div align="center">
                <div type="button" onclick="callpay()" >确认支付</div>
            </div>
            <!--<div @click="location.href='http://auction.jiangliping.com/api/goodspay/index?id='+item.id">付款</div><div>朋友代付</div>-->
        </div>
    </div>
</div>
<script src=" https://cdn.bootcss.com/vue/2.2.2/vue.min.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script>
    // 倒计时
    var time=document.getElementById("cheng").innerText;
    var date = new Date(time*1000);
    console.log(date)
    Y = date.getFullYear() + '-';
    M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
    D = date.getDate() + ' ';
    h = date.getHours() + ':';
    m = date.getMinutes() + ':';
    s = date.getSeconds();
    console.log(Y+M+D+h+m+s);
    document.getElementById("jiao").innerHTML=(Y+M+D+h+m+s)
    var t = setInterval(function(){
        var cheng=document.getElementById("cheng").innerText;
        var leftTime = 86400 ;
        var time=Math.floor((new Date()).valueOf()/1000)-cheng;
        var dao=leftTime-time;
        if(dao<0){
            clearInterval(t);
            document.getElementById("sheng").innerHTML="交易关闭"
        }else{
            var days = parseInt(dao / 1000 / 60 / 60 / 24 , 10); //计算剩余的天数
            var hours = parseInt(dao / 60 / 60 % 24 , 10); //计算剩余的小时
            var minutes = parseInt(dao / 60 % 60, 10);//计算剩余的分钟
            var seconds = parseInt(dao  % 60, 10);//计算剩余的秒数
            days = checkTime(days);
            hours = checkTime(hours);
            minutes = checkTime(minutes);
            seconds = checkTime(seconds);
            // console.log(leftTime)
            document.getElementById("timer").innerHTML = hours+"小时" + minutes+"分"+seconds+"秒";
            // console.log(hours)
        }
    },10);
    function checkTime(i){ //将0-9的数字前面加上0，例1变为01
        if(i<10)
        {
            i = "0" + i;
        }
        return i;
    }
    function jsApiCall()
    {
        var data=<?php echo $data; ?>;
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', data,
            //如果 用AJAX返回数据用下面这一行
            // 'getBrandWCPayRequest',$.parseJSON(data),
            function(res){
                WeixinJSBridge.log(res.err_msg);
                //alert('err_code:'+res.err_code+'err_desc:'+res.err_desc+'err_msg:'+res.err_msg);
                //alert(res.err_code+res.err_desc+res.err_msg);
                //alert(res);
                if(res.err_msg == "get_brand_wcpay_request:ok"){
                    alert("支付成功!");
                    // window.location.href="http://auction.jiangliping.com/home/order.html";
                    // window.location.href="http://auction.jiangliping.com/home/my.html";
                    window.history.go(-3);

                }else if(res.err_msg == "get_brand_wcpay_request:cancel"){
                    alert("用户取消支付!");
                }else{
                    alert("支付失败!");
                }
            }
        );
    }
    function callpay()
    {
        if (typeof WeixinJSBridge == "undefined"){
            if( document.addEventListener ){
                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
            }else if (document.attachEvent){
                document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
            }
        }else{
            jsApiCall();
        }
    }
</script>
</body>
</html>