<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto">
            <form method="get">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">名称</label>
                        <div class="layui-input-inline">
                            <input type="text" name="name" value="" placeholder="请输入" autocomplete="off"
                                   class="layui-input" id="name">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-list" type="button" id="searchBtn">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>搜索
                        </button>
                        <button class="layui-btn layuiadmin-btn-list data-remote rights_btn" type="button" data-remote-url="<?php echo U('setting/role/add');?>" data-remote-title="添加角色" data-remote-full="true" rights-flag="setting:role:add">
                            添加角色
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
        <a class="layui-btn layui-btn-normal layui-btn-xs data-remote rights_btn" data-remote-url="<?php echo U('setting/role/edit',['id'=>'']);?>{{d.id}}" data-remote-title="编辑角色" data-remote-full="true" rights-flag="setting:role:edit">编辑</a>
        {{# if(d.is_admin==2){ }}
        <a class="layui-btn layui-btn-danger layui-btn-xs delete_h rights_btn" data-id="{{d.id}}" rights-flag="setting:role:del">删除</a>
        {{# } }}
    </script>
    <script>
        function getSearchParam() {
            return {
                name: $("#name").val(),
            };
        }

        layui.table.render({
            elem: '#table'
            , maxHeight: 800  //表格高度
            , even: true    //隔行换色
            , url: '<?php echo U('/setting/role/index');?>' //数据接口
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
                {field: 'id', title: 'ID'},
                {field: 'name', title: '名称'},
                {field: 'admin_name', title: '超级管理员'},
                {field: 'handle', title: '操作',templet: '#titleTpl'},
            ]]
        });
        //搜索
        $("#searchBtn").on("click", function () {
            layui.table.reload('table', {page: {}, where: getSearchParam()});
        });
        //删除
        $(document).on("click", ".delete_h", function () {
            var id = $(this).attr("data-id");
            var title = $(this).text();
            layui.layer.confirm("确认"+title+"？",function(index){
                $.myAjaxPost("<?php echo U('setting/role/del');?>",{id:id})
            })
        });
    </script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>