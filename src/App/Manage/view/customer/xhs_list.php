<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <form method="get">
            <input type="hidden" name="id" value="<?php echo $id;?>">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                    </button>
                    <button class="layui-btn layuiadmin-btn-list data-remote rights_btn" type="button" data-remote-url="<?php echo U('customer/xhs_add');?>?id=<?php echo $id; ?>" data-remote-title="添加账号" rights-flag="customer:xhs_add">
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
        <a class="layui-btn layui-btn-danger layui-btn-xs delete_h rights_btn" data-id="{{d.id}}"  rights-flag="customer:xhs_del">删除</a>
    </script>
<script>
    function getSearchParam() {
        return {
            id: $("#id").val(),
        };
    }

    layui.table.render({
        elem: '#table'
        , maxHeight: 800  //表格高度
        , even: true    //隔行换色
        , url: '<?php echo U('/customer/xhs_list', ["id" => $id]);?>' //数据接口
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
            {field: 'id', title: 'id'},
            {field: 'xhs', title: '小红书号',sort:true},
            {field: 'nickname', title: '昵称'},
            {field: 'secret_id', title: '加密id'},
            {field: 'remark', title: '备注'},
            {field: 'handle', title: '操作',templet: '#titleTpl'},
        ]]
    });
    //搜索
    $("#searchBtn").on("click", function () {
        layui.table.reload('table', {page: {}, where: getSearchParam()});
    });
    function reloadTable(){
        layui.table.reload('table', {
            page: {
                curr:$("#layui-table-page1 .layui-laypage-em").next().html()
            }
            , where: getSearchParam()
        });
        layui.layer.closeAll(); //关闭当前弹窗
    }
    //删除
    $(document).on("click", ".delete_h", function () {
        var id = $(this).attr("data-id");
        var title = $(this).text();
        layui.layer.confirm("确认"+title+"？",function(index){
            $.myAjaxPost("<?php echo U('customer/xhs_del');?>",{id:id},{
                success: function (resp) {
                    if (resp.meta.code != undefined) {
                        if (resp.meta.code == 0) {
                            layui.layer.alert("操作成功", function () {
                                reloadTable();
                            });
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
        })
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>