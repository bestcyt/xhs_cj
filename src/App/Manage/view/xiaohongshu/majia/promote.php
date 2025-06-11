<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card" style="padding-bottom: 10px;">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php echo U('/xiaohongshu/majia/promote');?>" id="tenant_form">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">笔记分享链接</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <textarea name="note_url" placeholder="" class="layui-textarea" rows="4" ></textarea>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label" required>商家选择</label>
                    <div class="layui-input-inline">
                        <select name="account_id[]" lay-search id="account_id">
                            <option value="">--选择--</option>
                            <?php foreach ($account_arr as $key=>$value): ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['auth_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label" required>商家选择</label>
                    <div class="layui-input-inline">
                        <select name="account_id[]" lay-search id="account_id">
                            <option value="">--选择--</option>
                            <?php foreach ($account_arr as $key=>$value): ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['auth_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label" required>商家选择</label>
                    <div class="layui-input-inline">
                        <select name="account_id[]" lay-search id="account_id">
                            <option value="">--选择--</option>
                            <?php foreach ($account_arr as $key=>$value): ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['auth_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label" required>商家选择</label>
                    <div class="layui-input-inline">
                        <select name="account_id[]" lay-search id="account_id">
                            <option value="">--选择--</option>
                            <?php foreach ($account_arr as $key=>$value): ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['auth_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label" required>商家选择</label>
                    <div class="layui-input-inline">
                        <select name="account_id[]" lay-search id="account_id">
                            <option value="">--选择--</option>
                            <?php foreach ($account_arr as $key=>$value): ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['auth_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div id="textboxes"></div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">素人号被赞数量</label>
                    <div class="layui-input-inline" style="width: 100px;">
                        <input type="text" name="suren_dian_number"  autocomplete="off" placeholder="" class="layui-input" maxlength="10" value="" style="width: 100px;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">商家素人被赞数</label>
                    <div class="layui-input-inline"">
                        <input type="text" name="shangjia_suren_dian_number"  autocomplete="off" placeholder="" class="layui-input" maxlength="10" value="" style="width: 100px;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">商家素人被评数</label>
                    <div class="layui-input-inline" style="width: 400px;">
                        <input type="text" name="shangjia_suren_ping_number"  autocomplete="off" placeholder="" class="layui-input" maxlength="10" value="" style="width: 100px;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">帖子点赞数</label>
                    <div class="layui-input-inline"">
                        <input type="text" name="post_dian_number"  autocomplete="off" placeholder="" class="layui-input" maxlength="5" value="" style="width: 100px;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">帖子收藏数</label>
                    <div class="layui-input-inline"">
                        <input type="text" name="post_collect_number"  autocomplete="off" placeholder="" class="layui-input" maxlength="5" value="" style="width: 100px;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">帖子评论数</label>
                    <div class="layui-input-inline"">
                        <input type="text" name="post_ping_number"  autocomplete="off" placeholder="" class="layui-input" maxlength="10" value="" style="width: 100px;">
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
<script src="/layuiadmin/xm-select/xm-select.js"></script>
<script>
    //表单提交
    $("#tenant_form").myAjaxForm({
        success: function (resp) {
            if (resp.meta.code != undefined) {
                if (resp.meta.code == 0) {
                    layui.layer.alert(resp.response, function () {
                        parent.location.reload();
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
<style>
    .layui-form-label{width: 120px;}
</style>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>
