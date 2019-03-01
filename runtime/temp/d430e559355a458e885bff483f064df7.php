<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:76:"/data/wwwroot/aaa/public/../application/webcontroller/view/member/index.html";i:1537411791;s:58:"/data/wwwroot/aaa/application/webcontroller/view/base.html";i:1537411790;s:77:"/data/wwwroot/aaa/application/webcontroller/view/public/javascript_index.html";i:1537411792;}*/ ?>
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

<section class="content-header">
    <h1>会员列表</h1>
    <ol class="breadcrumb">
        <li class="active"><i class="fa fa-dashboard"></i>会员列表</li>
    </ol>
</section>
<div class="layui-content-box">
    <div class="box">
        <div class="box-header layui-row">
            <div class="layui-col-xs12 layui-col-sm6 layui-col-md6">

                <div class="input-group">
                    <button class="layui-btn layui-btn-sm layui-btn-normal monitor-action" data-action="create">
                        <i class="layui-icon">&#xe654;</i>新增
                    </button>

                    <!--<button class="layui-btn layui-btn-sm layui-btn-danger monitor-action" data-action="deleteAll">-->
                    <!--<i class="layui-icon">&#xe640;</i>批量删除-->
                    <!--</button>-->
                </div>
            </div>

            <!--<div class="layui-col-xs12 layui-col-sm6 layui-col-md6">-->
                <!--&lt;!&ndash;<div class="input-group">&ndash;&gt;-->
                <!--&lt;!&ndash;<input type="text" class="form-control" placeholder="请输入关键词">&ndash;&gt;-->
                <!--&lt;!&ndash;<span class="input-group-btn">&ndash;&gt;-->
                <!--&lt;!&ndash;<button class="btn btn-default" type="button">搜索</button>&ndash;&gt;-->
                <!--&lt;!&ndash;</span>&ndash;&gt;-->
                <!--&lt;!&ndash;</div>&ndash;&gt;-->
            <!--</div>-->
            <div class="demoTable" style="float:right;">
                <div class="layui-inline">
                    <input class="layui-input" name="nickname" id="empno" autocomplete="off"  required lay-verify="number" placeholder="输入大师称 或手机号">
                </div>
                <button class="layui-btn" data-type="reload">搜索</button>
            </div>
        </div>
        <div class="box-body">
            <span style="font-size: 25px;margin-left: 40px; ">会员总量 : <?php echo $member['membersum']; ?></span>
           <span style="font-size: 25px;margin-left: 100px;">被屏蔽总量 : <?php echo $member['shield']; ?></span>
            <br/>
             <span style="font-size:20px;margin-left: 40px;">1级 : <?php echo $member['level1']; ?></span>
             <span style="font-size:20px;margin-left: 40px;">2级 : <?php echo $member['level2']; ?></span>
             <span style="font-size:20px;margin-left: 40px;">3级 : <?php echo $member['level3']; ?></span>
             <span style="font-size:20px;margin-left: 40px;">4级 : <?php echo $member['level4']; ?></span>
             <span style="font-size:20px;margin-left: 40px;">5级 : <?php echo $member['level5']; ?></span>

            <table id="lists-table" lay-filter="lists"></table>
            <!--<script type="text/html" id="tb-status">-->
                <!--<i class='fa {{# if(d.status==1){ }}fa-check-circle text-green{{# }else{ }}fa-times-circle text-red{{# } }}' lay-event="status"></i>-->
            <!--</script>-->

            <script type="text/html" id="tb-status">
                {{# if (d.status==1) { }}
                正常
                {{# } }}
                {{# if (d.status!=1) { }}
                <p style="color:red;">禁用</p>
                {{# } }}

            </script>
            <!--操作-->
            <script type="text/html" id="tb-action">

                {{# if (d.status!=1){ }}
                <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="status">取消禁用</a>
                {{# } }}
                {{# if (d.status==1){ }}
                <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="status">禁用</a>
                {{# } }}

                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>

                <!--<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>-->
            </script>
        </div>
    </div>
</div>
<script type="text/javascript">
    /**
     * 一般直接写在一个js文件中
     * 公共方法
     */
    layui.use(['table','layer','jquery'], function(){
        var $=layui.jquery,table = layui.table,layer=layui.layer;
        // 表格初始化

        table.render({
            elem: '#lists-table'
            ,url:"<?php echo url(\think\Request::instance()->controller().'/getData'); ?>"
            ,cellMinWidth: 80 //全局定义常规单元格的最小宽度，layui 2.2.1 新增
            ,height:'full-145'
            ,page:true
            ,limit:15
            ,limits:[15,30,50,100,200,500,1000]
            ,response: {
                statusCode: 200 //成功的状态码，默认：0
            }
            ,cols: [
                <?php echo $table; ?>
            ]
        });
        // 表格内操作监听 编辑、删除
        table.on('tool(lists)', function(obj){
            var data = obj.data;
            if(obj.event === 'detail'){
                show_layui(layer,'<?php echo \think\Lang::get('detail'); ?>',"<?php echo url('detail'); ?>"+"?id="+data.id);
            } else if(obj.event === 'del'){
                layer.confirm('真的删除吗?', function(index){
                    if(data.status==1){
//                        layer.msg('<?php echo \think\Lang::get('del_is_use'); ?>',{icon:7});return false;
                        layer.msg('启用状态数据不可删除',{icon:7});return false;
                    }
                    $.ajax({
                        url:"<?php echo url('delete'); ?>"
                        ,data:{id:data.id}
                        ,type:'POST'
                        ,dataType:'json'
                        ,success:function (res) {
                            if(res.code==200){
                                obj.del();
                                layer.close(index);
                                layer.msg(res.msg, {icon: 1,time:1000});
                            }else{
                                layer.msg(res.msg,{icon:7});
                            }
                        }
                    });
                });
            } else if(obj.event === 'edit'){
                show_layui(layer,'<?php echo \think\Lang::get('edit'); ?>',"<?php echo url('edit'); ?>"+"?id="+data.id,800);
                //read 详情
            }else if(obj.event==='read'){
                show_layui(layer,'详情',"<?php echo url('read'); ?>"+"?id="+data.id);
            }else if(obj.event === 'info'){
                show_layui(layer,'订单详情',"<?php echo url('info'); ?>"+"?id="+data.id);
            }
            else if(obj.event === 'status'){
                var status=data.status==1?0:1;
                $.ajax({
                    url:"<?php echo url('updatestate'); ?>"
                    ,data:{id:data.id,status:status}
                    ,type:'POST'
                    ,dataType:'json'
                    ,success:function (res) {
                        if(res.code==200){
                            // layer.msg(res.msg, {icon: 1,time:1000});
                           successMsg(res.msg);
                            obj.update({
                                status: status
                            });
                        }else {
                            layer.msg(res.msg,{icon:7});
                        }
                    }
                });
            }else if(obj.event === 'status'){
                var status=data.status==1?0:1;
                $.ajax({
                    url:"<?php echo url('updatestate'); ?>"
                    ,data:{id:data.id,status:status}
                    ,type:'POST'
                    ,dataType:'json'
                    ,success:function (res) {
                        if(res.code==200){
                            //layer.msg(res.msg, {icon: 1,time:1000});
//                            successMsg(res.msg);
                            obj.update({
                                status: status
                            });
                        }else {
                            layer.msg(res.msg,{icon:7});
                        }
                    }
                });
            }
        });
//        表格数据编辑
        table.on('edit(lists)', function(obj){ //注：edit是固定事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data={};
            data['id']=obj.data.id;
            data[obj.field]=obj.value;
            $.ajax({
                url:"<?php echo url('edit'); ?>"
                ,data:data
                ,type:'POST'
                ,dataType:'json'
                ,success:function (res) {
                    if(res.code==0){
                        layer.msg(res.msg, {icon: 1,time:1000});
                    }else {
                        table.reload('lists-table');
                        layer.msg(res.msg,{icon:7});
                    }
                }
            })
        });
        // 新增和批量删除监听
        $('.monitor-action').on('click',function () {
            switch ($(this).data('action')){
                case 'create':
                    show_layui(layer,'<?php echo \think\Lang::get('create'); ?>',"<?php echo url('create'); ?>");
                    break;
                case 'deleteAll':

                    break;
                default:
            }
        });

    });
    /**
     * 新增成功提示并重载数据库
     * @param layer
     * @param msg
     */
    function successMsg(msg) {
        layui.use(['table','layer'], function() {
            var table = layui.table,layer=layui.layer;
            // 成功提示
            layer.msg(msg, {icon: 1,time:2000});
            // 重载数据库
            table.reload('lists-table');
        })
    }
</script>
<script type="text/javascript">
    layui.use(['table','layer','jquery'], function(){
        var $=table = layui.table;
        //搜索
        var $ = layui.$, active = {
            reload: function(){
                var nickname = $('#empno');

                //执行重载
                table.reload('lists-table', {
                    page: {
                        curr: 1 //重新从第 1 页开始
                    }
                    ,where: {
                        key: {
                            nickname: nickname.val(),
                        }
                    }
                });
            }
        };
        $('.box-header .layui-btn').on('click', function(){
            var type = $(this).data('type');
            active[type] ? active[type].call(this) : '';
        });
    });
</script>

</body>
</html>