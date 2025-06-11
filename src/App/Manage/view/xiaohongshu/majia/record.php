<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">商家名</label>
                    <div class="layui-input-inline">
                        <input type="text" name="auth_name" value="" placeholder="请输入" autocomplete="off" class="layui-input" id="auth_name">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">计划id</label>
                    <div class="layui-input-inline">
                        <input type="text" name="plan_id" value="" placeholder="计划id" autocomplete="off"
                               class="layui-input" id="plan_id">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">笔记链接</label>
                    <div class="layui-input-inline">
                        <input type="text" name="note_url" value="" placeholder="请输入" autocomplete="off"
                               class="layui-input" id="note_url">
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
                    <label class="layui-form-label">创建人</label>
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
                    <label class="layui-form-label">创建时间</label>
                    <div class="layui-input-inline" id="test-range" style="width: 416px;">
                        <div class="layui-input-inline">
                            <input type="text" id="startDate" class="layui-input" placeholder="开始时间" autocomplete="off" value="">
                        </div>
                        <div class="layui-form-mid">-</div>
                        <div class="layui-input-inline">
                            <input type="text" id="endDate" class="layui-input" placeholder="结束时间" autocomplete="off" value="">
                        </div>
                    </div>
                </div>
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                    <button class="layui-btn layuiadmin-btn-list data-remote rights_btn" type="button" data-remote-url="<?php echo U('xiaohongshu/majia/promote');?>" data-remote-title="提交马甲计划" data-remote-full="true" rights-flag="xiaohongshu:grow:promote">
                        提交马甲计划
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
        ,type: 'date'
        ,format: 'yyyy-MM-dd'
    });

    function getSearchParam() {
        return {
            note_url: $("#note_url").val(),
            status: $("#status").val(),
            fail_type: $("#fail_type").val(),
            time_begin: $("#startDate").val(),
            time_end: $("#endDate").val(),
            admin_id: $("#admin_id").val(),
            plan_id: $("#plan_id").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/xiaohongshu/majia/record');?>' //数据接口
        , page: true //开启分页
        , limit: 100
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
            {field: 'id', title: '计划id',width:80,fixed:"left"},
            {field: 'note_url', title: '链接',width:270,fixed:"left",templet:function(d){
                    return '<span class="clipboard" clipboard-data="'+d.note_url+'" style="color:cornflowerblue;cursor: pointer" onclick="window.open(\''+d.note_url+'\');">'+d.note_url+'</span>';
                }},
            {field: 'account_name', title: '关联商家'},
            {field: 'suren_dian_number', title: '素人点赞数',width:103},
            {field: 'shangjia_suren_dian_number', title: '商家素人点赞数',width:130},
            {field: 'shangjia_suren_ping_number', title: '商家素人评论数',width:130},
            {field: 'create_time', title: '创建时间',width:170},
            {field: 'admin_name', title: '创建人',width:80},
            {field: 'query_begin_time', title: '预查询时间',width:170},
            {field: 'query_red_id', title: '预查询人',width:170},
            {field: 'status_name', title: '状态',width:110,sort:true},
            {field: 'result', title: '备注',sort:true},
            {field: 'result1', title: '操作',fixed:"right",width:120,templet:function(d){
                var str = '<span class="layui-btn layui-btn-normal layui-btn-xs data-remote" data-remote-title="计划id：'+d.id+'" data-remote-url="<?php echo U('/xiaohongshu/majia/result',['plan_id'=>""]);?>'+d.id+'" data-remote-full="true">结果</span>';
                if (d.status < 2) {
                    str += '<a class="layui-btn layui-btn-danger layui-btn-xs delete_h" data-id="' + d.id + '">删除</a>'
                }else if(d.status ==3 || d.status == 7){
                    str += '<a class="layui-btn layui-btn-danger layui-btn-xs reload_h" data-id="' + d.id + '">重跑</a>'
                }
                return str
                }},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });
    //删除
    $(document).on("click", ".delete_h", function () {
        var id = $(this).attr("data-id");
        layui.layer.confirm("确认删除？",function(index){
            $.myAjaxPost("<?php echo U('xiaohongshu/majia/delete');?>",{id:id},{
                success:function () {
                    layui.table.reload('table', {
                        page: {
                            curr:$("#layui-table-page1 .layui-laypage-em").next().html()
                        }
                        , where: getSearchParam()
                    });
                    layer.close(index); //关闭当前弹窗
                }
            })
        })
    });
    //重跑
    $(document).on("click", ".reload_h", function () {
        var id = $(this).attr("data-id");
        layui.layer.confirm("确认重跑？",function(index){
            $.myAjaxPost("<?php echo U('xiaohongshu/majia/redo');?>",{id:id},{
                success:function () {
                    layui.table.reload('table', {
                        page: {
                            curr:$("#layui-table-page1 .layui-laypage-em").next().html()
                        }
                        , where: getSearchParam()
                    });
                    layer.close(index); //关闭当前弹窗
                }
            })
        })
    });
    //数量统计
    $(document).on("click", ".total_overview", function () {
        $.myAjaxPost("<?php echo U('xiaohongshu/majia/overview');?>",{},{
            success:function (resp) {
                if (resp.meta.code != undefined) {
                    if (resp.meta.code == 0) {
                        layui.layer.alert(resp.response);
                    } else if (resp.meta.msg != undefined && resp.meta.msg) {
                        layui.layer.alert(resp.meta.msg);
                    } else {
                        layui.layer.alert("操作失败");
                    }
                } else {
                    layui.layer.alert("返回结果异常");
                }
            }
        })
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>