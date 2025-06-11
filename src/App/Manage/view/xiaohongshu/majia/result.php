<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline" style="display: none;">
                    <label class="layui-form-label">计划id</label>
                    <div class="layui-input-inline">
                        <input type="text" name="plan_id" value="<?php echo $plan_id;?>" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="plan_id">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">状态</label>
                    <div class="layui-input-inline">
                        <select name="status" lay-search id="status">
                            <option value="999">--选择--</option>
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
                </div>
            </div>
        </form>
    </div>
    <div class="layui-card-body">
        <table id="table" lay-filter="testTable"></table>
    </div>
</div>
<script>
    function getSearchParam() {
        return {
            plan_id: $("#plan_id").val(),
            status: $("#status").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/xiaohongshu/majia/result');?>' //数据接口
        , page: true //开启分页
        , limit: 500
        , height: "full-120"
        , toolbar:true
        , defaultToolbar: ['filter']
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
            {field: 'id', title: 'id', width:130,templet:function(d){
                    return '<span class="temp_kkk_kkk_'+d.flag+'">'+d.id+'</span>';
                }},
            {field: 'suren_nick', title: '素人账号'},
            {field: 'suren_ping_content', title: '素人评论内容'},
            {field: 'type_name', title: '操作', width:80},
            {field: 'handle_account', title: '操作账号', sort:true},
            {field: 'handle_content', title: '操作评论内容'},
            {field: 'dispatch_time', title: '领取时间'},
            {field: 'handle_time', title: '操作时间'},
            {field: 'status_name', title: '状态', width:80, sort:true},
            {field: 'result', title: '异常信息'},
            // {field: 'again_id', title: '重试ID',templet:function(d){
            //     if(d.again_id>0){
            //         return d.again_id;
            //     }else{
            //         return "";
            //     }
            //     }},
            // {field: 'parent_id', title: '源ID',templet:function (d) {
            //         if(d.parent_id>0){
            //             return d.parent_id;
            //         }else{
            //             return "";
            //         }
            //     }},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });
</script>
    <style>
        .layui-table-cell{
            height: unset;
        }
    </style>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>