<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">手机号</label>
                    <div class="layui-input-inline">
                        <input type="text" name="mobile" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="mobile">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">姓名</label>
                    <div class="layui-input-inline">
                        <input type="text" name="real_name" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="real_name">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">ID</label>
                    <div class="layui-input-inline">
                        <input type="text" name="id" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="id">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-inline">
                        <select name="status" lay-search id="status">
                            <option value="">--选择--</option>
                            <?php foreach ($status_arr as $key=>$value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                    <button class="layui-btn layuiadmin-btn-list data-remote rights_btn" type="button" data-remote-url="<?php echo U('setting/account/add');?>" data-remote-title="添加账号" rights-flag="setting:account:add">
                        添加账号
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="layui-card-body">
        <table id="table" lay-filter="testTable"></table>
    </div>
</div>
<script type="text/html" id="titleTpl">
    <a class="layui-btn layui-btn-normal layui-btn-xs data-remote rights_btn" data-remote-url="<?php echo U('setting/account/edit',['id'=>'']);?>{{d.id}}" data-remote-title="编辑账号"  rights-flag="setting:account:edit">编辑</a>
    {{# if(d.is_root != 1){ }}
        <a class="layui-btn layui-btn-xs data-remote rights_btn" data-remote-url="<?php echo U('setting/account/assign_roles',['id'=>'']);?>{{d.id}}" data-remote-title="分配角色" data-remote-full="true" rights-flag="setting:account:assign_roles">分配角色</a>
        {{# if(d.status == 1){ }}
        <a class="layui-btn layui-btn-danger layui-btn-xs delete_h rights_btn" data-id="{{ d.id }}"  rights-flag="setting:account:status">禁用</a>
        {{# }else{ }}
        <a class="layui-btn layui-btn-danger layui-btn-xs delete_h rights_btn" data-id="{{ d.id }}"  rights-flag="setting:account:status">启用</a>
        {{# } }}
    {{# } }}
    <a class="layui-btn layui-btn-warm layui-btn-xs data-remote rights_btn" data-remote-url="<?php echo U('setting/account/set_password',['id'=>'']);?>{{d.id}}" data-remote-title="重置密码"  rights-flag="setting:account:set_password">重置密码</a>
</script>
<script>
    function getSearchParam() {
        return {
            real_name: $("#real_name").val(),
            mobile: $("#mobile").val(),
            id: $("#id").val(),
            status: $("#status").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/setting/account/index');?>' //数据接口
        , page: true //开启分页
        , limit: 100
        , cellMinWidth: 100
        , limits: [20, 40, 50, 80, 100, 500]
        , request: {
            limitName: 'count' //每页数据量的参数名，默认：limit
        }
        , where: getSearchParam()
        , parseData: function (res) {
            return {
                "code": res.meta.code,
                "msg": res.meta.msg,
                "count": res.response.total,
                "data": res.response.data
            }
        }
        , cols: [[ //表头
            {field: 'id', title: 'ID',width:80},
            {field: 'mobile', title: '手机号',width:220},
            {field: 'real_name', title: '姓名'},
            {field: 'status_name', title: '状态',width:100},
            {field: 'root_name', title: 'ROOT用户',width:100},
            {field: 'create_time_format', title: '创建时间'},
            {field: 'handle', title: '操作',width:300,templet: '#titleTpl'},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });
    //启用 禁用
    $(document).on("click", ".delete_h", function () {
        var id = $(this).attr("data-id");
        var title = $(this).text();
        layui.layer.confirm("确认"+title+"？",function(index){
            $.myAjaxPost("<?php echo U('setting/account/status');?>",{id:id},{
                success:function () {
                    layui.table.reload('table', {page: {}, where: getSearchParam()});
                    layer.close(index); //关闭当前弹窗
                }
            })
        })
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>