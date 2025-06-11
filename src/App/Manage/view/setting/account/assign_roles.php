<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto" style="padding-top: 0;padding-bottom: 0;">
        <div class="layui-form-item">
            <div class="layui-inline" style="font-weight: bold;">
                姓名：<code style="color:red;"><?php echo $acct_info["real_name"];?></code>&nbsp;&nbsp;&nbsp;&nbsp;
                手机号：<code style="color:red;"><?php echo $acct_info["mobile"];?></code>
            </div>
        </div>
    </div>
    <div class="layui-card-body">
        <form method="post" action="<?php echo U('setting/account/assign_roles');?>" id="tenant_form"/>
        <input type="hidden" name="id" value="<?php echo $acct_info['id']; ?>">
        <div class="layui-form-item">
            <table id="table" lay-filter="testTable"></table>
        </div>
        <div class="layui-form-item">
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
<script>
    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , page: false //开启分页
        , limit: 10000
        , cellMinWidth: 100
        , data:<?php echo json_encode($all_roles);?>
        , cols: [[ //表头
            {field: 'role_ids[]', title: '选择',templet:function (d) {
                var checked=d.checked?"checked":"";
                return '<div><input type="checkbox" lay-skin="primary" name="role_ids[]" value="'+d.id+'" '+checked+'></div>'
            }},
            {field: 'id', title: 'ID'},
            {field: 'name', title: '名称'},
            {field: 'admin_name', title: '超管'},
            {field: 'handle', title: '操作',templet:function (d) {
                return '<div><a class="layui-btn layui-btn-normal layui-btn-xs data-remote" data-remote-url="<?php echo U('setting/role/show_rights',['id'=>'']);?>'+d.id+'" data-remote-title="角色权限" data-remote-full="true">查看</a></div>'
            }},
        ]]
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>