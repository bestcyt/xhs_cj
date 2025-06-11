<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">话术</label>
                    <div class="layui-input-inline">
                        <input type="text" name="content" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="content">
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
                    <label class="layui-form-label">归属员工</label>
                    <div class="layui-input-inline">
                        <select name="admin_id" lay-search id="admin_id">
                            <option value="">--选择--</option>
                            <?php foreach ($admin_arr as $value): ?>
                                <option value="<?php echo $value["id"]; ?>"><?php echo $value["real_name"]; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                </div>
                <button class="layui-btn layuiadmin-btn-list data-remote rights_btn" type="button" data-remote-url="<?php echo U('xiaohongshu/remark/add');?>" data-remote-title="添加话术" rights-flag="xiaohongshu:remark:add">
                    添加话术
                </button>
            </div>
        </form>
    </div>
    <div class="layui-card-body">
        <table id="table" lay-filter="testTable"></table>
    </div>
</div>
<script type="text/html" id="titleTpl">
    <a class="layui-btn layui-btn-normal layui-btn-xs data-remote rights_btn" data-remote-url="<?php echo U('xiaohongshu/remark/edit',['id'=>'']);?>{{d.id}}" data-remote-title="编辑" rights-flag="xiaohongshu:remark:edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs delete_h rights_btn" rights-flag="xiaohongshu:remark:delete"  data-id="{{ d.id }}">删除</a>
</script>
<script>
    function getSearchParam() {
        return {
            content: $("#content").val(),
            status: $("#status").val(),
            admin_id: $("#admin_id").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/xiaohongshu/remark/index');?>' //数据接口
        , page: true //开启分页
        , limit: 100
        , cellMinWidth: 100
        , height: "full-150"
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
            {field: 'content', title: '话术'},
            {field: 'admin_name', title: '创建人'},
            {field: 'status_name', title: '状态'},
            {field: 'handle', title: '操作',templet: '#titleTpl'},
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
            $.myAjaxPost("<?php echo U('xiaohongshu/remark/delete');?>",{id:id},{
                success:function () {
                    layui.table.reload('table', {page: {}, where: getSearchParam()});
                    layer.close(index); //关闭当前弹窗
                }
            })
        })
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>