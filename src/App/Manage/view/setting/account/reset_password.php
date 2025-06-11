<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
        <div class="layui-card">
            <div class="layui-card-header">修改密码</div>
            <div class="layui-card-body" pad15>
                <div class="layui-form" lay-filter="">
                    <form method="post" id="passwordForm" action="/setting/account/reset_password">
                    <div class="layui-form-item">
                        <label class="layui-form-label">当前密码</label>
                        <div class="layui-input-inline">
                            <input type="password" name="oldPassword" lay-verify="required" lay-verType="tips" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">新密码</label>
                        <div class="layui-input-inline">
                            <input type="password" name="password" lay-verify="required|pass|password" lay-verType="tips" autocomplete="off" id="LAY_password" class="layui-input">
                        </div>
                        <div class="layui-form-mid layui-word-aux">6到15个字符</div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">确认新密码</label>
                        <div class="layui-input-inline">
                            <input type="password" name="repassword" lay-verify="required|repass|password" lay-verType="tips" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button class="layui-btn" lay-submit lay-filter="setmypass">确认修改</button>
                        </div>
                    </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
    <script>
        //表单提交
        $("#passwordForm").myAjaxForm({
            success: function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        layui.layer.alert("操作成功", function () {
                            window.top.location="<?php echo U('logout/index');?>";
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
    </script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>