<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:75:"/data/wwwroot/aaa/public/../application/webcontroller/view/index/index.html";i:1537411791;s:67:"/data/wwwroot/aaa/application/webcontroller/view/public/header.html";i:1537411792;s:70:"/data/wwwroot/aaa/application/webcontroller/view/public/left_menu.html";i:1537411792;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title><?php echo \think\Lang::get('admin_title'); ?></title>
    <link rel="stylesheet" href="/static/webadmin/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/static/webadmin/bootstrap/css/font-awesome.min.css">
    <link rel="stylesheet" href="/static/webadmin/css/AdminLTE.min.css">
    <link rel="stylesheet" href="/static/lib/layui-v2.2.45/css/layui.css">
    <link rel="stylesheet" href="/static/webadmin/css/style.css">
</head>
<body class="skin-blue fixed sidebar-mini">
<div class="wrapper">
    <!--头部-->
    <header class="main-header">
        <a class="a-href logo" href="javascript:;" data-href="<?php echo url('content'); ?>"><span class="logo-mini"><?php echo \think\Lang::get('admin_title_abb'); ?></span><span class="logo-lg"><?php echo \think\Lang::get('admin_title'); ?></span></a>
<nav class="navbar navbar-static-top">
    <a href="jsvascript:;" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <!--<span class="sr-only">Toggle navigation</span>-->
        <!--<span class="icon-bar"></span>-->
        <!--<span class="icon-bar"></span>-->
        <!--<span class="icon-bar"></span>-->
    </a>
    <!--<div style="display: inline-block;">-->
        <!--<ul class="layui-nav">-->
            <!--<li class="layui-nav-item">-->
                <!--<a href="<?php echo url('@index'); ?>" target="_blank">网站首页</a>-->
            <!--</li>-->
        <!--</ul>-->
    <!--</div>-->
    <div class="navbar-custom-menu">
        <ul class="layui-nav layui-layout-right">
            <li class="layui-nav-item">
                <a href="javascript:;">
                    <!--<img src="http://t.cn/RCzsdCq" class="layui-nav-img">-->
                    <?php echo cookie('name'); ?>
                </a>
                <dl class="layui-nav-child">
                    <dd><a class="a-href" href="javascript:;" data-href="<?php echo url('admin/adminedit'); ?>">账号设置</a></dd>
                </dl>
            </li>
            <li class="layui-nav-item"><a href="javascript:;" class="clean-cache">清除缓存</a></li>
            <li class="layui-nav-item"><a href="<?php echo url('login/login_out'); ?>">退出</a></li>
        </ul>
    </div>
</nav>
    </header>
    <!--左边菜单-->
    <aside class="main-sidebar" style="overflow-x: hidden;height: 100%;">
        <section class="sidebar "  style="overflow-x: hidden;position: relative;width: 247px;height: 100%">
    <ul class="layui-nav layui-nav-tree" lay-filter="test" style="width: 230px;">
        <?php if(is_array($treeMenu) || $treeMenu instanceof \think\Collection || $treeMenu instanceof \think\Paginator): $i = 0; $__LIST__ = $treeMenu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$oo): $mod = ($i % 2 );++$i;if($oo['level'] == '1' && $oo['name'] == 'Welcome/index'): ?>
        <li class="layui-nav-item"><a class="a-href" href="javascript:;" data-href="<?php echo url(MODULE_NAME.'/'.$oo['name']); ?>"><i class="fa <?php echo $oo['icon']; ?>"></i><span><?php echo $oo['title']; ?></span></a></li>
        <?php elseif($oo['level'] == '1'): ?>
        <li class="layui-nav-item">
            <a href="javascript:;"><i class="fa <?php echo $oo['icon']; ?>"></i><span><?php echo $oo['title']; ?></span></a>
            <dl class="layui-nav-child">
                <?php if(is_array($treeMenu) || $treeMenu instanceof \think\Collection || $treeMenu instanceof \think\Paginator): $i = 0; $__LIST__ = $treeMenu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$to): $mod = ($i % 2 );++$i;if($to['pid'] == $oo['id']): ?>
                <dd><a class="a-href" href="javascript:;" data-href="<?php echo url(MODULE_NAME.'/'.$to['name'],$to['name_additional']); ?>"><i class="fa <?php echo $to['icon']; ?>"></i><?php echo $to['title']; ?></a></dd>
                <?php endif; endforeach; endif; else: echo "" ;endif; ?>
            </dl>
        </li>
        <?php endif; endforeach; endif; else: echo "" ;endif; ?>
        <!--<li class="layui-nav-item"><a class="a-href" href="javascript:;" data-href="<?php echo url('@categroy'); ?>"><i class="fa fa-home"></i><span>案例分类管理</span></a></li>-->
        <!--<li class="layui-nav-item">-->
            <!--<a href="javascript:;"><i class="fa fa-users"></i><span>案例管理</span></a>-->
            <!--<dl class="layui-nav-child">-->
                <!--<dd><a class="a-href" href="javascript:;" data-href="<?php echo url('categroy/index'); ?>"><i class="fa fa-user-o"></i>分类管理</a></dd>-->
                <!--<dd><a class="a-href" href="javascript:;" data-href="<?php echo url('tag/index'); ?>"><i class="fa fa-tags"></i>标签管理</a></dd>-->
                <!--<dd><a class="a-href" href="javascript:;" data-href="<?php echo url('cases/index'); ?>"><i class="fa fa-vcard"></i>案例信息</a></dd>-->
                <!--<dd><a class="a-href" href="javascript:;" data-href="<?php echo url('index/icon'); ?>"><i class="fa layui-icon">&#xe62e;</i>图标</a></dd>-->
            <!--</dl>-->
        <!--</li>-->
    </ul>
</section>

    </aside>
    <!--内容区域-->
    <div class="content-wrapper" style="overflow: hidden;position: relative">
        <div class="loding" style="display: none">
            <i class="layui-icon layui-anim loding-rotate layui-anim-loop">&#xe63d;</i>
            加载中.....
        </div>
        <iframe class="iframe" src="<?php echo url('index/content'); ?>" frameborder="0" style="width: 100%;height: 100%;"></iframe>
    </div>
</div>
<script src="/static/lib/layui-v2.2.45/layui.js"></script>
<script type="text/javascript">
    //JavaScript代码区域
    layui.use(['element','jquery','layer'], function(){
        var $=layui.jquery,layer=layui.layer;
        $('.a-href').on('click',function () {
            $('.loding').show();
            $('.iframe').attr('src',$(this).data('href'));
            $('.iframe').load(function () {
                $('.loding').hide();
            })
        });
        $('.iframe').load(function () {
            $('.loding').hide();
        });
        // 内容盒子大小
        $('.content-wrapper').css({'height':$(window).height() + 'px'});
        $('.loding').css({'height':$(window).height() + 'px'});
        // 菜单栏显示和影藏效果
        var a=1,a1=1;
        $('.sidebar-toggle').click(function () {
            var win_width=$(window).width();
            if(a==1 && win_width >= 768){
                a=2;
                $('body').addClass('sidebar-collapse');
            }else if(a1==1 && win_width < 768){
                a1=2;
                $('body').addClass('sidebar-open');
            }else if(a1==2 && win_width < 768){
                a1=1;
                $('body').removeClass('sidebar-open');
            }else {
                a=1;
                $('body').removeClass('sidebar-collapse');
            }
        });
        // 菜单hover效果
        $('.main-sidebar').hover(function () {
            if(a==2 && $(window).width()>=768){
                $('body').addClass('sidebar-collapse-on-hover').removeClass('sidebar-collapse');
            }
        },function () {
            if(a==2 && $(window).width()>=768){
                $('body').removeClass('sidebar-collapse-on-hover').addClass('sidebar-collapse');
            }
        });
        // 清除缓存
        $('.clean-cache').click(function () {
            $.ajax({
                url:"<?php echo url('index/cleancache'); ?>",
                type:'POST',
                dataType:'json',
                success:function (data) {
                    if(data.code==200){
                        layer.msg('缓存清除成功！',{icon: 1,time:2000});
                    }else {
                        layer.msg(data.message,{icon: 7,time:2000});
                    }
                }
            });
        });
    });
</script>
</body>
</html>
