<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-row layui-col-space15">
    <div class="layui-col-md12">
        <div class="layui-card">
            <div class="layui-card-body" pad15>
                <div class="layui-form" lay-filter="">
                    <form method="post" id="passwordForm" action="/setting/rights/edit">
                        <input type="hidden" name="id" value="<?php echo $resource_row["id"];?>"/>
                        <div class="layui-form-item">
                            <label class="layui-form-label">唯一标识符</label>
                            <div class="layui-input-inline">
                                <input type="text" name="flag" lay-verify="required|maxlength" class="layui-input" lay-maxlength="50" autocomplete="off" value="<?php echo $resource_row["flag"];?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="name" lay-verify="required|maxlength" class="layui-input" lay-maxlength="50" autocomplete="off" value="<?php echo $resource_row["name"];?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-inline">
                                <input type="radio" name="show_type" value="1" title="页面" <?php if($resource_row["show_type"]==1):?>checked<?php endif;?>>
                                <input type="radio" name="show_type" value="2" title="按钮" <?php if($resource_row["show_type"]==2):?>checked<?php endif;?>>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">前端路由</label>
                            <div class="layui-input-inline">
                                <input type="text" name="front_url" lay-verify="maxlength" class="layui-input" lay-maxlength="100" autocomplete="off" value="<?php echo $resource_row["front_url"];?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">前台图标</label>
                            <div class="layui-input-inline">
                                <input type="text" name="icon" lay-verify="maxlength" class="layui-input" lay-maxlength="50" autocomplete="off" value="<?php echo $resource_row["icon"];?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">备注</label>
                            <div class="layui-input-inline">
                                <textarea name="remark" lay-verify="maxlength" lay-maxlength="255" placeholder="" class="layui-textarea" rows="4"><?php echo $resource_row["remark"];?></textarea>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">涉及接口</label>
                            <div class="layui-input-inline">
                                <textarea name="api" lay-verify="maxlength" lay-maxlength="2000" placeholder="多个用换行隔开" class="layui-textarea" rows="4"><?php echo implode(PHP_EOL,$api_arr);?></textarea>
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
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>