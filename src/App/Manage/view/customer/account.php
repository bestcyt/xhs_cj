<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">商家名</label>
                    <div class="layui-input-inline">
                        <input type="text" name="auth_name" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="auth_name">
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                    <button class="layui-btn layuiadmin-btn-list data-remote" type="button" data-remote-url="<?php echo U('customer/account_add');?>" data-remote-title="添加商家">
                        添加商家
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
    <a class="layui-btn layui-btn-normal layui-btn-xs data-remote rights_btn" data-remote-url="<?php echo U('/customer/xhs_list',['id'=>'']);?>{{d.id}}" data-remote-title="{{d.auth_name}} - 小红书号管理"  data-remote-full="true" rights-flag="customer:xhs_home">小红书号管理</a>
</script>
<script>
    function getSearchParam() {
        return {
            auth_name: $("#auth_name").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/customer/account');?>' //数据接口
        , page: true //开启分页
        , limit: 100
        , height: "full-120"
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
            {field: 'auth_name', title: '商家名称', sort:true},
            {field: 'xhs_number', title: '小红书账号数量',sort:true},
            {field: 'handle', title: '操作',templet: '#titleTpl',width:265},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });

</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>