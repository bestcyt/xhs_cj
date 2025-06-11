<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="<?php echo U('setting/role/add');?>" id="tenant_form">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label required">名称</label>
                    <div class="layui-input-inline">
                        <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="请输入名称" class="layui-input" maxlength="20" value="" style="width: 500px;">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <?php foreach($access_rows as $key=>$value):?>
                <table class="layui-table api_access_table" lay-even lay-skin="line">
                    <colgroup>
                        <col>
                        <col width="100">
                    </colgroup>
                    <thead>
                    <tr>
                        <th style="font-weight: bold">权限设置</th>
                        <th style="font-weight: bold"><input type="checkbox" lay-skin="primary" id="checkAll"></th>
                    </tr>
                    </thead>
                    <tbody class="api_access_table_body">
                    <?php echo recycle_tree_list($value);?>
                    </tbody>
                </table>
                <?php endforeach;?>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <div class="layui-footer layui-layout-admin">
                        <button class="layui-btn" lay-submit>立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(function () {
        //表单提交
        $("#tenant_form").myAjaxForm();
    });
</script>
<script>
    $(function () {
        //展开和收起
        $(".api_access_table").find(".api_access_table_body").find("tr").on("click",function(e){
            if($(e.target).closest("td").hasClass("tree_level_a")){
                return;
            }
            if($(e.target).closest("td").hasClass("tree_level_b")){
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
            $str.='<tr style="cursor: pointer;" id="tree_api_id_'.$value["id"].'" data-parent_id="'.$value["parent_id"].'" data-level="'.$value["level"].'" class="tree_level_'.$value["level"].'"><td style="font-weight: bold;padding-left:'.($buffer_left*8).'px"><span class="fa fa-chevron-down show_hide_menu">&nbsp;</span>'.$value["name"].'('.$value["flag"].')</td>';
            $str.='<td class="tree_level_a"><input type="checkbox" name="node_ids[]" value="'.$value["id"].'" lay-skin="primary" class="tree_level_abc"></td>';
            $str.='</tr>'.recycle_tree_list($value["children"]);
        }else{
            $str.='<tr style="cursor: pointer;" id="tree_api_id_'.$value["id"].'" data-parent_id="'.$value["parent_id"].'" data-level="'.$value["level"].'" class="tree_level_'.$value["level"].'"><td style="padding-left:'.($buffer_left*8).'px">'.$value["name"].'('.$value["flag"].')</td>';
            $str.='<td class="tree_level_a"><input type="checkbox" name="node_ids[]" value="'.$value["id"].'" lay-skin="primary" class="tree_level_abc"></td>';
            $str.='</tr>';
        }
    }
    return $str;
}
?>
<script>
    $(document).on("click",".layui-form-checkbox",function () {
        var checked=$(this).hasClass("layui-form-checked");
        //是否全选按钮
        if($(this).closest("th").length>0){
            var buffer_tr=$(".api_access_table_body").find("tr");
            for(var x=0;x<buffer_tr.length;x++){
                //选中或者不选中
                $(buffer_tr[x]).find(".tree_level_abc").prop("checked",checked);
                if(checked){
                    $(buffer_tr[x]).find(".layui-form-checkbox").addClass("layui-form-checked");
                }else{
                    $(buffer_tr[x]).find(".layui-form-checkbox").removeClass("layui-form-checked");
                }
            }
            return;
        }

        //上级和下级的联动
        var parent=$(this).closest("tbody");
        var level=parseInt($(this).closest("tr").attr("data-level"));
        var index=$(this).closest("tr").index();
        var buffer_gt_tr=parent.find("tr:gt("+index+")");
        var buffer_lt_tr=parent.find("tr:lt("+index+")");
        //向下
        for(var x=0;x<buffer_gt_tr.length;x++){
            if(parseInt($(buffer_gt_tr[x]).attr("data-level"))<=level){break;}
            //选中或者不选中
            $(buffer_gt_tr[x]).find(".tree_level_abc").prop("checked",checked);
            if(checked){
                $(buffer_gt_tr[x]).find(".layui-form-checkbox").addClass("layui-form-checked");
            }else{
                $(buffer_gt_tr[x]).find(".layui-form-checkbox").removeClass("layui-form-checked");
            }
        }
        //向上
        var cursor_level=level-1;
        for(var x=buffer_lt_tr.length-1;x>=0;x--){
            if(!checked){
                break;
            }
            if(cursor_level<1){
                break;
            }
            var temp_level=parseInt($(buffer_lt_tr[x]).attr("data-level"));
            if(temp_level!=cursor_level){
                continue;
            }
            cursor_level--;
            //选中必须勾选
            $(buffer_lt_tr[x]).find(".tree_level_abc").prop("checked",true);
            $(buffer_lt_tr[x]).find(".layui-form-checkbox").addClass("layui-form-checked");
        }
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>
