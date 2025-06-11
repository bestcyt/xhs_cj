<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-form layui-card-header layuiadmin-card-header-auto">
        <div class="layui-form-item">
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <select  name="filter_key" id="system_id_select"  lay-search>
                        <?php foreach($system_arr as $key=>$value):?>
                            <option value="<?php echo $key;?>"<?php if($system_id==$key):?> selected<?php endif;?>><?php echo $value;?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
            <div class="layui-inline" style="float: right;">
                <button class="layui-btn layuiadmin-btn-list data-remote rights_btn" type="button" data-remote-url="<?php echo U('setting/rights/add');?>" data-remote-title="添加权限"  rights-flag="setting:rights:handle">
                    添加权限
                </button>
            </div>
        </div>
    </div>
    <div class="layui-card-body">
        <div class="table-responsive" style="overflow-x:auto;">
            <?php foreach($access_rows as $key=>$value):?>
                <form action="" method="post" id="api_access_set_form_<?php echo $key;?>">
                    <table class="layui-table api_access_table" lay-even lay-skin="line" id="api_access_table_<?php echo $key;?>" <?php if($key>0):?>style="display: none;" <?php endif;?>>
                        <colgroup>
                            <col>
                            <col width="300">
                        </colgroup>
                        <thead>
                        <tr>
                            <th style="font-weight: bold">菜单</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody class="api_access_table_body">
                        <?php echo recycle_tree_list($value);?>
                        </tbody>
                    </table>
                </form>
            <?php endforeach;?>
        </div><!-- table-responsive -->
    </div>
</div>
<script>
    $(function () {
        //展开和收起
        $(".api_access_table").find(".api_access_table_body").find("tr").on("click",function(e){
            if($(e.target).hasClass("tree_level_a")){
                return;
            }
            if($(e.target).hasClass("tree_level_b")){
                return;
            }
            var show_hide_menu=$(this).find(".show_hide_menu");
            if(show_hide_menu.length<=0){
                return false;
            }
            show_hide_menu.toggleClass("fa-chevron-down").toggleClass("fa-chevron-right");
            var parent=$(this).closest("tbody");
            var level=parseInt($(this).attr("data-level"));
            var index=$(this).index();
            var buffer_tr=parent.find("tr:gt("+index+")");
            if(show_hide_menu.hasClass("fa-chevron-right")){
                for(var x=0;x<buffer_tr.length;x++){
                    if(parseInt($(buffer_tr[x]).attr("data-level"))<=level){break;}
                    $(buffer_tr[x]).hide();
                }
            }else{
                for(var y=0;y<buffer_tr.length;y++){
                    if(parseInt($(buffer_tr[y]).attr("data-level"))<=level){break;}
                    var this_parent=$("#tree_api_id_"+$(buffer_tr[y]).attr("data-parent_id"));
                    if(this_parent.find(".fa-chevron-down").length>0 && this_parent.is(":visible")){
                        $(buffer_tr[y]).show();
                    }
                }
            }
        });
        //切换系统
        $("#system_id_select").on("change",function () {
            window.location="<?php echo U('/setting/rights/index');?>?system_id="+$(this).val();
        });
        //删除菜单
        $(".delete_menu").on("click",function () {
            var add_menu_parent_id = $(this).attr("data-add_menu_id");
            layui.layer.confirm("确认删除？一旦删除将不可撤销！！！", function () {
                $.ajax({
                    url: "<?php echo U('setting/rights/del');?>",
                    type: "post",
                    async: false,
                    data: {"id": add_menu_parent_id},
                    success: function (data) {
                        window.location.reload();
                    }
                });
            });
        });
        //上移菜单
        $(".up_menu").on("click",function () {
            var add_menu_parent_id=$(this).attr("data-add_menu_id");
            layui.layer.confirm("确认上移？",function () {
                $.ajax({
                    url:"<?php echo U('setting/rights/up');?>",
                    type:"post",
                    async:false,
                    data:{"id":add_menu_parent_id},
                    success:function(data){
                        window.location.reload();
                    }
                });
            });
        });
        //下移菜单
        $(".down_menu").on("click",function () {
            var add_menu_parent_id=$(this).attr("data-add_menu_id");
            layui.layer.confirm("确认下移？",function () {
                $.ajax({
                    url:"<?php echo U('setting/rights/down');?>",
                    type:"post",
                    async:false,
                    data:{"id":add_menu_parent_id},
                    success:function(data){
                        window.location.reload();
                    }
                });
            });
        });
    });
</script>
    <style>
        .show_hide_menu{display: inline-block;width: 39px;}
        .fa-chevron-down{background: url("/images/down.png") center center no-repeat}
        .fa-chevron-right{background: url("/images/up.png") center center no-repeat}
        .api_access_table a{color:#0288d1!important;}
        .api_access_table a:hover{text-decoration: underline;}
    </style>
<?php
//递归格式
function recycle_tree_list(array $data){
    $str="";
    foreach($data as $value){
        $buffer_left=($value['level']-1)*3+1;
        if($value["children"]){
            $str.='<tr style="cursor: pointer;" id="tree_api_id_'.$value["id"].'" data-parent_id="'.$value["parent_id"].'" data-level="'.$value["level"].'" class="tree_level_'.$value["level"].'"><td style="font-weight: bold;padding-left:'.($buffer_left*8).'px"><span class="fa fa-chevron-down show_hide_menu">&nbsp;&nbsp;</span>&nbsp;'.$value["name"].'('.$value["flag"].')</td>';
            $str.='<td>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b add_menu data-remote rights_btn" data-remote-url="'.U('setting/rights/add',['parent_id'=>$value["id"],"system_id"=>$value["system_id"]]).'" data-remote-title="添加子权限" rights-flag="setting:rights:handle">添加子权限</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b edit_menu data-remote rights_btn" data-remote-url="'.U('setting/rights/edit',['id'=>$value["id"]]).'" data-remote-title="编辑" rights-flag="setting:rights:handle">编辑</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b delete_menu rights_btn" rights-flag="setting:rights:handle">删除</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b up_menu rights_btn" rights-flag="setting:rights:handle">上移</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b down_menu rights_btn" rights-flag="setting:rights:handle">下移</a>';
            $str.='</td></tr>'.recycle_tree_list($value["children"]);
        }else{
            $str.='<tr style="cursor: pointer;" id="tree_api_id_'.$value["id"].'" data-parent_id="'.$value["parent_id"].'" data-level="'.$value["level"].'" class="tree_level_'.$value["level"].'"><td style="padding-left:'.($buffer_left*8).'px">'.$value["name"].'('.$value["flag"].')</td>';
            $str.='<td>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b add_menu data-remote rights_btn" data-remote-url="'.U('setting/rights/add',['parent_id'=>$value["id"],"system_id"=>$value["system_id"]]).'" data-remote-title="添加子权限" rights-flag="setting:rights:handle">添加子权限</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b edit_menu data-remote rights_btn" data-remote-url="'.U('setting/rights/edit',['id'=>$value["id"]]).'" data-remote-title="编辑" rights-flag="setting:rights:handle">编辑</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b delete_menu rights_btn" rights-flag="setting:rights:handle">删除</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b up_menu rights_btn" rights-flag="setting:rights:handle">上移</a>';
            $str.='<a href="javascript:;" style="margin-left:8px;" data-add_menu_id="'.$value["id"].'" class="tree_level_b down_menu rights_btn" rights-flag="setting:rights:handle">下移</a>';
            $str.='</td></tr>';
        }
    }
    return $str;
}
?>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>