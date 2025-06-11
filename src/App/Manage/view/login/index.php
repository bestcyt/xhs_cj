<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>小红书点赞</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/login.css" media="all">
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">
    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>小红书点赞</h2>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
                <input type="text" name="mobile" id="LAY-user-login-username" lay-verify="required" placeholder="手机号" class="layui-input" value="<?php echo $remember_account["account"]??"";?>">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required" placeholder="密码" class="layui-input" value="<?php echo $remember_account["password"]??"";?>">
            </div>
            <div class="layui-form-item">
                <div class="layui-row">
                    <div class="layui-col-xs7">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>
                        <input type="text" name="code" id="LAY-user-login-vercode" lay-verify="required" placeholder="图形验证码" class="layui-input" value="<?php echo $remember_account?"1234":"";?>">
                    </div>
                    <div class="layui-col-xs5">
                        <div style="margin-left: 10px;">
                            <img src="<?php echo U('/login/code');?>?pure=1&t=1517378797" class="layadmin-user-login-codeimg" data-code="<?php echo U('/login/code',["pure"=>1]);?>" id="LAY-user-get-vercode">
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item" style="margin-bottom: 20px;">
                <input type="checkbox" name="remember" lay-skin="primary" title="记住密码" <?php if(!empty($remember_account)):?>checked<?php endif;?>>
                <div class="layui-unselect layui-form-checkbox" lay-skin="primary"><span>记住密码</span><i class="layui-icon layui-icon-ok"></i></div>
                <a href="<?php echo U('/login/forget');?>" class="layadmin-user-jump-change layadmin-link" style="margin-top: 7px;display: none;">忘记密码？</a>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit" id="login_submit">登 入</button>
            </div>
        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        <p>© 2023 <a href="#">www.sssyydss.com</a></p>
    </div>
</div>

<script src="/layuiadmin/layui/layui.js?12dw23422"></script>
<script>
    layui.config({
        base: '/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function(){
        var $ = layui.$
            ,setter = layui.setter
            ,admin = layui.admin
            ,form = layui.form
            ,router = layui.router()
            ,search = router.search;

        form.render();

        //提交
        form.on('submit(LAY-user-login-submit)', function(obj){

            //请求登入接口
            admin.req({
                url: '<?php echo U("/login/index");?>'//实际使用请改成服务端真实接口
                ,type:"POST"
                ,data: obj.field
                ,done: function(resp){
                    if (window.self !== window.top) {
                        window.location=resp.response.url;
                    }else{
                        window.location = "<?php echo U("/");?>";
                    }
                }
            });
        });

        //实际使用时记得删除该代码
        // layer.msg('为了方便演示，用户名密码可随意输入', {
        //     offset: '15px'
        //     ,icon: 1
        // });
        <?php if(!empty($remember_account)):?>
        document.getElementById("login_submit").click();
        <?php endif;?>

    });
</script>
</body>
</html>