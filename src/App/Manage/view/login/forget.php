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
    <script src="/js/jquery-1.11.1.min.js"></script>
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <script src="https://cdn.staticfile.org/html5shiv/r29/html5.min.js"></script>
    <script src="https://cdn.staticfile.org/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">
    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>小红书点赞</h2>
        </div>
        <form method="post" action="<?php echo U('login/forget',['type'=>2]);?>" id="forgetForm">
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-email" for="LAY-login-cellphone"></label>
                <input type="text" name="email" id="LAY-login-cellphone" lay-verify="required|email" placeholder="请输入注册时的邮箱" class="layui-input">
            </div>
            <div class="layui-form-item">
                <div class="layui-row">
                    <div class="layui-col-xs7">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-login-vercode"></label>
                        <input type="text" name="code" id="LAY-login-vercode" lay-verify="required" placeholder="图形验证码" class="layui-input" autocomplete="off">
                    </div>
                    <div class="layui-col-xs5">
                        <div style="margin-left: 10px;">
                            <img src="/login/code?width=128&pure=1time=1" style="cursor: pointer;" onclick="this.src=this.src+'a'">
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-row">
                    <div class="layui-col-xs7">
                        <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-login-smscode"></label>
                        <input type="text" name="verify_code" id="LAY-login-smscode" lay-verify="required" placeholder="邮件验证码" class="layui-input" autocomplete="off">
                    </div>
                    <div class="layui-col-xs5">
                        <div style="margin-left: 10px;">
                            <button type="button" class="layui-btn layui-btn-primary layui-btn-fluid" id="LAY-getsmscode">获取邮件验证码</button>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="token" value="" id="checkToken"/>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-forget-submit">找回密码</button>
            </div>
        </div>
        </form>
        <form method="post" action="<?php echo U('login/forget',['type'=>3]);?>" id="forgetForm2" style="display: none;">
            <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-login-password"></label>
                    <input type="password" name="password" id="LAY-login-password" lay-verify="required|pass|password" placeholder="新密码" class="layui-input" autocomplete="off">
                </div>
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-login-repass"></label>
                    <input type="password" name="repassword" id="LAY-login-repass" lay-verify="required|password" placeholder="确认密码" class="layui-input" autocomplete="off">
                </div>
                <input type="hidden" value="" name="token" id="checkToken2">
                <div class="layui-form-item">
                    <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-forget-resetpass">重置新密码</button>
                </div>
            </div>
        </form>

        <div class="layui-trans layui-form-item layadmin-user-login-other" style="padding-top: 0">
            <a href="<?php echo U('login/index');?>" class="layadmin-user-jump-change layadmin-link layui-hide-xs">用已有帐号登入</a>
        </div>
    </div>

    <div class="layui-trans layadmin-user-login-footer">
        <p>© 2023 <a href="#">www.sssyydss.com</a></p>
    </div>

</div>

<script src="/layuiadmin/layui/layui.js?12dw23422"></script>
<script>
    //拓展表单验证
    layui.form.verify({
        //金额
        money: function (value, item) {
            if (value === "") {
                return;
            }
            var money = /^\d+(\.\d{0,2})?$/;
            if (!money.test(value)) {
                return "请填写正确金额";
            }
        },
        //最小长度
        minlength: function (value, item) {
            if (value === "") {
                return;
            }
            var min = item.getAttribute('lay-minlength');
            if (value.length < min) {
                return '不能小于' + min + '个字符的长度';
            }
        },
        //最大长度
        maxlength: function (value, item) {
            if (value === "") {
                return;
            }
            var min = item.getAttribute('lay-maxlength');
            if (value.length > min) {
                return '不能大于' + min + '个字符的长度';
            }
        },
        //最小值
        min: function (value, item) {
            if (value === "") {
                return;
            }
            var min = item.getAttribute('lay-min');
            if (value < min) {
                return '不能小于' + min;
            }
        },
        //最大值
        max: function (value, item) {
            if (value === "") {
                return;
            }
            var max = item.getAttribute('lay-max');
            if (value > max) {
                return '不能大于' + min;
            }
        },
        //输入长度必须介于 5 和 10 之间的字符串（汉字算一个字符）
        rangelength: function (value, item) {
            if (value === "") {
                return;
            }
            var rangelength = item.getAttribute('lay-rangelength');
            rangelength = rangelength.split(",");
            if (value.length < rangelength[0]) {
                return '输入长度必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
            }
            if (value.length > rangelength[1]) {
                return '输入长度必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
            }
        },
        //输入值必须介于 5 和 10 之间。
        range: function (value, item) {
            if (value === "") {
                return;
            }
            var rangelength = item.getAttribute('lay-range');
            rangelength = rangelength.split(",");
            if (value < rangelength[0]) {
                return '输入值必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
            }
            if (value > rangelength[1]) {
                return '输入长度必须介于 ' + rangelength[0] + ' 和 ' + rangelength[1] + ' 之间';
            }
        },
        //密码
        password: function (value, item) {
            if (value === "") {
                return;
            }
            var reg = /^[0-9A-Za-z_!@#\$\%\^\&\*\(\)]{6,15}$/;
            if (reg.test(value) && !/^\d{6,15}$/.test(value) && !/^[a-zA-Z]{6,15}$/.test(value) && !/^_{6,15}$/.test(value)) {
                return;
            }
            return "6-15位数字、字母、_！@#￥%^&*（）组成";
        },
    });
    //输入框的最大长度
    $(document).on("input", "input[type='number']", function () {
        var maxlength = $(this).attr("maxlength");
        if (maxlength === undefined) {
            return;
        }
        maxlength = parseInt(maxlength);
        var value = $(this).val();
        if (value.length > maxlength) {
            $(this).val(value.slice(0, maxlength));
        }
    });
    //ajax 表单提交
    $.fn.myAjaxForm = function (options) {
        var thisForm = $(this);
        var laySubmit = thisForm.find("[lay-submit]");
        if (laySubmit.length < 1) {
            return false;
        }
        var id = $(laySubmit).attr("lay-filter");
        if (typeof id === "undefined" || id === undefined || id === "") {
            var rand_key = new Date().valueOf().toString() + "_" + Math.round(Math.random() * 80 + 200).toString();
            id = "form_" + rand_key;
            $(laySubmit).attr("lay-filter", id);
        }
        layui.form.on('submit(' + id + ')', function (obj) {
            var defaults = {
                url: thisForm.attr("action"),
                type: thisForm.attr("method") ? thisForm.attr("method") : "post",
                dataType: 'json',
                async: false,
                data: thisForm.serialize(),
                success: function (resp) {
                    if (resp.meta.code != undefined) {
                        if (resp.meta.code == 0) {
                            layui.layer.alert("操作成功", function () {
                                if (window.self !== window.top && window.parent !== window.top) {
                                    parent.location.reload();
                                } else {
                                    window.location.reload();
                                }
                            });
                        } else if (resp.meta.msg != undefined && resp.meta.msg) {
                            layui.layer.alert(resp.meta.msg);
                        } else {
                            layui.layer.alert("操作失败");
                        }
                    } else {
                        layui.layer.alert("返回结果异常");
                    }
                },
                error: function (resp) {
                    layui.layer.alert('请求失败');
                }
            };
            var opts = $.extend(defaults, options);
            $.ajax(opts);
            return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
        });
    };
</script>
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
            ,router = layui.router();

        form.render();
        $("#forgetForm2").myAjaxForm({
            success: function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        layui.layer.alert("设置成功",function () {
                            window.location="<?php echo U('/login/index');?>";
                        });
                    } else if (resp.meta.msg != undefined && resp.meta.msg) {
                        layui.layer.alert(resp.meta.msg);
                    } else {
                        layui.layer.alert("操作失败");
                    }
                } else {
                    layui.layer.alert("返回结果异常");
                }
            },
        });
        //提交验证
        $("#forgetForm").myAjaxForm({
            success: function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        //进入到下一步
                        $("#forgetForm").hide();
                        $("#forgetForm2").show();
                        $("#checkToken2").val(resp.response);
                    } else if (resp.meta.msg != undefined && resp.meta.msg) {
                        layui.layer.alert(resp.meta.msg);
                    } else {
                        layui.layer.alert("操作失败");
                    }
                } else {
                    layui.layer.alert("返回结果异常");
                }
            },
        });

        $("#LAY-getsmscode").click(function(){
            if($(this).hasClass("layui-disabled")){
                return;
            }
            if($("#LAY-login-cellphone").val()=="" || $("#LAY-login-vercode").val()==""){
                layui.layer.msg("请输入邮箱和图形验证码");
                return;
            }
            $.ajax({
                url:"<?php echo U('login/forget',['type'=>1]);?>",
                type:"post",
                dataType: 'json',
                async:false,
                data:$("#forgetForm").serialize(),
                success: function (resp) {
                    if (resp.meta.code != undefined) {
                        if (resp.meta.code == 0) {
                            layui.layer.msg("验证码已发送到您邮箱，请注意查收");
                            $("#checkToken").val(resp.response);
                            $(this).addClass("layui-disabled");
                            //倒计时
                            t($("#LAY-getsmscode"),60);
                        } else if (resp.meta.msg != undefined && resp.meta.msg) {
                            layui.layer.alert(resp.meta.msg);
                        } else {
                            layui.layer.alert("操作失败");
                        }
                    } else {
                        layui.layer.alert("返回结果异常");
                    }
                },
                error: function (resp) {
                    layui.layer.alert('请求失败');
                }
            })
        });
        function t(obj,second){
            if (second <= 0) {
                obj.removeClass("layui-disabled").html("重新获取");
                return
            }
            second--;
            obj.html(second + "秒后重新获取");
            setTimeout(function () {
                t(obj,second);
            },1000)
        }
    });
</script>
</body>
</html>