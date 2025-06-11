<?php \Mt\App\Manage\Helper\Layout::headerLayUi();?>
<div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <form class="layui-form" method="post" action="" id="tenant_form">
            <div class="layui-form-item">
                <?php foreach($access_rows as $key=>$value):?>
                <table class="layui-table api_access_table" lay-even lay-skin="line">
                    <colgroup>
                        <col>
                    </colgroup>
                    <thead>
                    <tr>
                        <th style="font-weight: bold">权限设置</th>
                    </tr>
                    </thead>
                    <tbody class="api_access_table_body">
                    <?php echo recycle_tree_list($value,$role_row);?>
                    </tbody>
                </table>
                <?php endforeach;?>
            </div>
        </form>
    </div>
</div>
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
function recycle_tree_list(array $data,&$role_row){
    $str="";
    foreach($data as $value){
        $buffer_left=($value['level']-1)*3+1;
        if(!in_array($value["id"], $role_row["nodes"]) && $role_row["is_admin"]==\Mt\Model\Manage\ManageRoleModel::ADMIN_NO){
            continue;
        }
        if($value["children"]){
            $str.='<tr style="cursor: pointer;" id="tree_api_id_'.$value["id"].'" data-parent_id="'.$value["parent_id"].'" data-level="'.$value["level"].'" class="tree_level_'.$value["level"].'"><td style="font-weight: bold;padding-left:'.($buffer_left*8).'px"><span class="fa fa-chevron-down show_hide_menu">&nbsp;</span>'.$value["name"].'('.$value["flag"].')</td>';
            $str.='</tr>'.recycle_tree_list($value["children"],$role_row);
        }else{
            $str.='<tr style="cursor: pointer;" id="tree_api_id_'.$value["id"].'" data-parent_id="'.$value["parent_id"].'" data-level="'.$value["level"].'" class="tree_level_'.$value["level"].'"><td style="padding-left:'.($buffer_left*8).'px">'.$value["name"].'('.$value["flag"].')</td>';
            $str.='</tr>';
        }
    }
    return $str;
}
?>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi();?>
