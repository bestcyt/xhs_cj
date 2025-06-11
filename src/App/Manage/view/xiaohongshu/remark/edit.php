<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php echo U('/xiaohongshu/remark/edit');?>" id="tenant_form">
            <div class="layui-form-item">
                <input type="hidden" name="remark_id" value="<?php echo $remark_id;?>">
                <div class="layui-inline" style=";">
                    <label class="layui-form-label required">备注名</label>
                    <div class="layui-input-inline">
<textarea rows="6" cols="30" name="content" class="layui-textarea" style="width: 300px;">
<?php echo $remark_info["content"];?>
</textarea>
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
