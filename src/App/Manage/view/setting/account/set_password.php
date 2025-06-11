<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
        <div class="layui-card">
            <div class="layui-card-body" pad15>
                <div class="layui-form" lay-filter="">
                    <form method="post" id="passwordForm" action="/setting/account/set_password">
                    <input type="hidden" name="id" value="<?php echo $account["id"];?>">
                    <div class="layui-form-item">
                        <label class="layui-form-label">手机号</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" value="<?php echo $account["mobile"];?>" readonly>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">姓名</label>
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input" value="<?php echo $account["real_name"];?>" readonly>
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
        $("#passwordForm").myAjaxForm();
    </script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>