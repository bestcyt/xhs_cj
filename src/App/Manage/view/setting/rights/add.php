<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-body" pad15>
                    <div class="layui-form" lay-filter="">
                        <form method="post" id="passwordForm" action="/setting/rights/add">
                            <input type="hidden" name="system_id" value="<?php echo $system_id;?>"/>
                            <input type="hidden" name="parent_id" value="<?php echo $parent_id;?>"/>
                            <div class="layui-form-item">
                                <label class="layui-form-label">唯一标识符</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="flag" lay-verify="required|maxlength" class="layui-input" lay-maxlength="50" autocomplete="off">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">名称</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="name" lay-verify="required|maxlength" class="layui-input" lay-maxlength="50" autocomplete="off">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">类型</label>
                                <div class="layui-input-inline">
                                    <input type="radio" name="show_type" value="1" title="页面" checked>
                                    <input type="radio" name="show_type" value="2" title="按钮">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">前端路由</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="front_url" lay-verify="maxlength" class="layui-input" lay-maxlength="100" autocomplete="off">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">前台图标</label>
                                <div class="layui-input-inline">
                                    <input type="text" name="icon" lay-verify="maxlength" class="layui-input" lay-maxlength="50" autocomplete="off">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">备注</label>
                                <div class="layui-input-inline">
                                    <textarea name="remark" lay-verify="maxlength" lay-maxlength="255" placeholder="" class="layui-textarea" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">涉及接口</label>
                                <div class="layui-input-inline">
                                    <textarea name="api" lay-verify="maxlength" lay-maxlength="2000" placeholder="多个用换行隔开" class="layui-textarea" rows="4"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-input-block">
                                    <button class="layui-btn" lay-submit lay-filter="setmypass">确认添加</button>
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