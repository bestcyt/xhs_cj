<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-card-body">
        <div class="layui-input-block layui-form" id="dateSelectDiv" style="float: right;width: 200px;z-index:99;">
            <select id="dateSelect" lay-filter="select1">
                <?php foreach($date_arr as $key=>$value):?>
                    <option value="<?php echo $key;?>"><?php echo $value;?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="layui-tab layui-tab-brief" lay-filter="tableTab" style="position: relative;">
            <span style="color:red;position: absolute;left: 200px;top: 9px"><?php echo current(array_values($date_arr));?> 的意外退出可能是还没执行完毕，不一定是意外退出</span>
            <ul class="layui-tab-title">
                <li class="layui-this">时长/内存</li>
                <li>意外退出</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-row">
                        <table id="table1" lay-filter="table1"></table>
                    </div>
                </div>
                <div class="layui-tab-item">
                    <div class="layui-row">
                        <table id="table2" lay-filter="table2"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    //tab切换重新渲染
    layui.element.on('tab(tableTab)', function (data) {
        renderTable();
    });
    var resp = null;

    function renderTable() {
        layui.table.render({
            elem: '#table1'
            , limit: 10000
            , even: true    //隔行换色
            , cols: [[
                {field: 'file', title: '运行脚本',width:600},
                {field: 'max_run_time', title: '最大运行时长(s)',templet:function(d){
                        return parseFloat(d.max_run_time).toFixed(2);
                    }},
                {field: 'max_memory_usage', title: '最大占用内存(M)',templet:function (d) {
                        return parseFloat(parseInt(d.max_memory_usage)/1024000).toFixed(2);
                    }},
            ]]
            , data: resp.warn
        });
        layui.table.render({
            elem: '#table2'
            , limit: 10000
            , even: true    //隔行换色
            , cols: [[
                {field: 'server_ip', title: 'server_ip'},
                {field: 'file', title: '脚本pid'},
                {field: 'cmd', title: '运行脚本',width:600},
                {field: 'start_at', title: '运行时间'},
            ]]
            , data: resp.fatal
        });
    }

    //搜索
    function search(){
        var ymd=$("#dateSelect").val();
        $.myAjaxGet("<?php echo U('setting/queue/cron',['date'=>'']);?>"+ymd,{},{
            success: function (result) {
                if (result.meta.code != undefined) {
                    if (result.meta.code == 0) {
                        resp = result.response;
                        renderTable();
                    } else if (result.meta.msg != undefined && result.meta.msg) {
                        layui.layer.alert(result.meta.msg);
                    } else {
                        layui.layer.alert("操作失败");
                    }
                } else {
                    layui.layer.alert("返回结果异常");
                }
            },
        });
    }
    layui.form.on('select(select1)', function (data) {
        search();
    });
    search();
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>