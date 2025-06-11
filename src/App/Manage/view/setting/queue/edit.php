<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php echo U('/setting/queue/edit',['id'=>$id]);?>" id="tenant_form">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">队列文件</label>
                    <div class="layui-input-inline">
                        <input type="text" name="file" lay-verify="required" autocomplete="off" placeholder="请输入脚本文件路径" class="layui-input" maxlength="100" value="<?php echo $info["file"]??"";?>">
                    </div>
                    <div style="color: red;font-size: 12px;padding-left: 110px;">App/Script/Queue 下的文件,如 Alert/mail</div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-inline">
                        <input type="text" name="remark" autocomplete="off" placeholder="请输入备注" class="layui-input" maxlength="100" value="<?php echo $info["remark"]??"";?>">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label required">任务数量</label>
                    <div class="layui-input-inline">
                        <input type="text" name="number" lay-verify="required|number|range" lay-range="1,1000" autocomplete="off" placeholder="请输入任务数量" class="layui-input" maxlength="11" value="<?php echo $info["number"]??"";?>">
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