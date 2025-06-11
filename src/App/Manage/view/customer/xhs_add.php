<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php echo U('/customer/xhs_add');?>" id="tenant_form">
            <div class="layui-form-item">
                <input type="hidden" name="id" value="<?php echo $id;?>">
                <div class="layui-inline">
                    <label class="layui-form-label required">小红书号</label>
                    <div class="layui-input-inline">
                        <input type="text" name="xhs" lay-verify="required" autocomplete="off" placeholder="请输入小红书号" class="layui-input" maxlength="32" value="">
                    </div>
                </div>
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
