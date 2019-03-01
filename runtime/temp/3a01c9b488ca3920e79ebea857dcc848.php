<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:75:"/data/wwwroot/aaa/public/../application/webcontroller/view/login/index.html";i:1537411791;s:58:"/data/wwwroot/aaa/application/webcontroller/view/base.html";i:1537411790;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title><?php echo \think\Lang::get('title_common'); ?>--<?php echo \think\Lang::get('title'); ?></title>
    <link rel="stylesheet" href="/static/webadmin/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/webadmin/bootstrap/css/font-awesome.min.css">
    <link rel="stylesheet" href="/static/lib/layui-v2.2.45/css/layui.css">
    <link rel="stylesheet" href="/static/webadmin/css/style.css">
    <script type="text/javascript" src="/static/lib/zepto/zepto.js"></script>
    <script src="/static/lib/layui-v2.2.45/layui.js"></script>
    <script src="/static/webadmin/js/js.js"></script>

    <style>
        .c-red{
            color:red;
            font-size: 18px;
            height: 38px;
            line-height: 30px;
            float: left;
        }
    </style>
</head>
<body class="lay-body layui-anim layui-anim-fadein">

<div class="admin-login-box">
    <div class="admin-login-content">
        <div class="admin-login-header">
            <h2>拍卖-后台管理</h2>
        </div>
        <form class="layui-form" action="">
            <div class="admin-login-form">
                <div class="layui-form-item">
                    <label class="admin-login-icon login-icon-username" for="login-username"></label>
                    <input type="text" id="login-username" lay-verify="required" name="username"  placeholder="用户名" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label class="admin-login-icon login-icon-password" for="login-password"></label>
                    <input type="text" id="login-password" lay-verify="required" name="password" placeholder="密码" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <div class="layui-row">
                        <div class="layui-col-md6">
                            <label class="admin-login-icon login-icon-code" for="login-code"></label>
                            <input type="text" id="login-code" autocomplete="off" name="code" placeholder="验证码" class="layui-input">
                        </div>
                        <div class="layui-col-md6">
                            <div class="admin-login-code">
                                <img src="<?php echo captcha_src(); ?>" alt="" height="38" class="admin-login-code-img">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <button class="layui-btn w-max" lay-submit="" lay-filter="submit">登 入</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="/static/lib/layui-v2.2.45/layui.js"></script>
<script>
    //一般直接写在一个js文件中
    layui.use(['layer', 'form','jquery'], function(){
        var layer=layui.layer,$=layui.jquery,form=layui.form;
        $('.admin-login-code-img').click(function () {
            this.src='/captcha.html?'+Math.random();
        });
        form.on('submit(submit)',function (data) {
            console.log(data.field);
            $.ajax({
                url:"<?php echo url('login/index'); ?>",
                data:data.field,
                type:'POST',
                dataType:'json',
                success:function (data) {
                    if(data.code==200){
                        layer.load(1);
                        setTimeout(function(){
                            layer.closeAll('loading');
                            window.location.href="<?php echo url('index/index'); ?>";
                        }, 600);
                    }else if(data.code==400){
                        layer.msg(data.msg, {
                            icon: 5,
                            time: 1500 //2秒关闭（如果不配置，默认是3秒）
                        });
                    }
                }
            });
            return false;
        })

    });
</script>

</body>
</html>