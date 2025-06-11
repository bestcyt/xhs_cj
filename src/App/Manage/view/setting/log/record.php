<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">类型</label>
                    <div class="layui-input-inline">
                        <select name="type" lay-search id="type">
                            <option value="">--选择--</option>
                            <?php foreach ($type_arr as $key=>$value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">时间</label>
                    <div class="layui-input-inline" id="test-range" style="width: 416px;">
                        <div class="layui-input-inline">
                            <input type="text" id="startDate" class="layui-input" placeholder="开始时间" autocomplete="off" value="<?php echo date("Y-m-d H:i:s",time()-86400);?>">
                        </div>
                        <div class="layui-form-mid">-</div>
                        <div class="layui-input-inline">
                            <input type="text" id="endDate" class="layui-input" placeholder="结束时间" autocomplete="off" value="<?php echo date("Y-m-d H:i:s",time());?>">
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="layui-card-body">
        <table id="table" lay-filter="testTable"></table>
    </div>
</div>
<script>
    layui.laydate.render({
        elem: '#test-range' //开始时间和结束时间所在 input 框的父选择器
        //设置开始日期、日期日期的 input 选择器
        ,range: ['#startDate', '#endDate'] //数组格式为 layui 2.6.6 开始新增
        ,type: 'datetime'
        ,format: 'yyyy-MM-dd HH:mm:ss'
    });
    function getSearchParam() {
        return {
            type: $("#type").val(),
            time_begin: $("#startDate").val(),
            time_end: $("#endDate").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/setting/log/record');?>' //数据接口
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
            {field: 'type_name', title: '类型',width:110},
            {field: 'name', title: '操作'},
            {field: 'create_time', title: '时间',width:167},
            {field: 'msg', title: '详情'},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });

</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>