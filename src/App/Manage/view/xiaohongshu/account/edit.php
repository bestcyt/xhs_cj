<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php echo U('/xiaohongshu/account/edit');?>" id="tenant_form">
            <div class="layui-form-item">
                <input type="hidden" name="id" value="<?php echo $account_row["id"];?>">
                <div class="layui-inline">
                    <label class="layui-form-label required">点赞</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="is_dian">
                            <?php foreach($dian_arr as $key=>$value):?>
                                <option value="<?php echo $key;?>"<?php if($account_row["is_dian"]==$key):?> selected<?php endif;?>><?php echo $value;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label required">评论</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="is_ping">
                            <?php foreach($ping_arr as $key=>$value):?>
                                <option value="<?php echo $key;?>"<?php if($account_row["is_ping"]==$key):?> selected<?php endif;?>><?php echo $value;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label required">收藏</label>
                    <div class="layui-input-inline">
                        <select class="layui-select" name="is_collect">
                            <?php foreach($collect_arr as $key=>$value):?>
                                <option value="<?php echo $key;?>"<?php if($account_row["is_collect"]==$key):?> selected<?php endif;?>><?php echo $value;?></option>
                            <?php endforeach;?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">风控信息</label>
                    <div class="layui-input-inline">
                        <textarea class="layui-textarea" name="risk_info" rows="4"><?php echo $account_row['risk_info'];?></textarea>
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
        $("#tenant_form").myAjaxForm({
            success: function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        layui.layer.alert("操作成功", function () {
                            parent.render_table();
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
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>
