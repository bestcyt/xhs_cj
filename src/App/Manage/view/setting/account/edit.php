<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php if($account):?>/setting/account/edit<?php else:?>/setting/account/add<?php endif;?>" id="tenant_form">
            <div class="layui-form-item">
                <input type="hidden" name="id" value="<?php echo $account["id"]??"";?>">
                <div class="layui-inline">
                    <label class="layui-form-label required">手机号</label>
                    <div class="layui-input-inline">
                        <input type="text" name="mobile" lay-verify="required" autocomplete="off" placeholder="请输入手机号" class="layui-input" maxlength="12" value="<?php echo $account["mobile"]??"";?>">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label required">姓名</label>
                    <div class="layui-input-inline">
                        <input type="text" name="real_name" lay-verify="required" autocomplete="off" placeholder="请输入姓名" class="layui-input" maxlength="20" value="<?php echo $account["real_name"]??"";?>">
                    </div>
                </div>
                <?php if(empty($account)):?>
                <div class="layui-inline">
                    <label class="layui-form-label required">密码</label>
                    <div class="layui-input-inline">
                        <input type="text" name="password" lay-verify="required|password" autocomplete="off" placeholder="请输入密码" class="layui-input" maxlength="16">
                    </div>
                </div>
                <?php endif;?>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <div class="layui-footer layui-layout-admin">
                        <button class="layui-btn" lay-submit>立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(function () {
        //表单提交
        $("#tenant_form").myAjaxForm();
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>
