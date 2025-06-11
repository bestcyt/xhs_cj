<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">小红书号</label>
                    <div class="layui-input-inline">
                        <input type="text" name="red_id" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="red_id">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">小红书昵称</label>
                    <div class="layui-input-inline">
                        <input type="text" name="nickname" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="nickname">
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                    <button class="layui-btn layuiadmin-btn-list download_btn" type="button" id="exportBtn">
                        <i class="layui-icon layui-icon-download-circle layuiadmin-button-btn "></i>导出Excel
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
    <a class="layui-btn layui-btn-normal layui-btn-xs data-remote" data-remote-url="<?php echo U('xiaohongshu/account/edit',['id'=>'']);?>{{d.id}}" data-remote-title="编辑账号">编辑</a>
</script>
<script>
    function getSearchParam() {
        return {
            red_id: $("#red_id").val(),
            nickname: $("#nickname").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/xiaohongshu/account/index');?>' //数据接口
        , page: true //开启分页
        , limit: 100
        , height: "full-180"
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
            {field: 'red_id', title: '小红书号'},
            {field: 'nickname', title: '小红书昵称'},
            {field: 'secret_id', title: '加密id'},
            {field: 'dian_name', title: '点赞',width:65},
            {field: 'ping_name', title: '评论',width:65},
            {field: 'collect_name', title: '收藏',width:65},
            {field: 'heartbeat_time', title: '心跳时间',sort:true,width:180,templet:function(d){
                if(d.logout){
                    return '<span style="color:red;">'+d.heartbeat_time+'</span>';
                }else{
                    return d.heartbeat_time;
                }
                }},
            {field: 'risk_info', title: '风控信息',sort:true},
            {field: 'handle', title: '操作',templet: '#titleTpl',width:80},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });
    function render_table(){
        layui.table.reload('table', {
            page: {
                curr:$("#layui-table-page1 .layui-laypage-em").next().html()
            }
            , where: getSearchParam()
        });
        layer.closeAll(); //关闭当前弹窗
    }
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>