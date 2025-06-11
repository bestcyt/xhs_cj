<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<?php
function show_class($is_red, $is_yellow) {
    if ($is_red) {
        echo 'c_red';
    } elseif ($is_yellow){
        echo 'c_orange';
    } else {
        echo '';
    }
}

function get_mem_used($mem_byte) {
    return round($mem_byte/1024/1024, 4). 'MB';
}

function RGBToHex($a, $b, $c){
    $match = [$a, $b, $c];
    $hexColor = "#";
    $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
    for ($i = 0; $i < 3; $i++) {
        $r = null;
        $c = $match[$i];
        $hexAr = array();
        while ($c > 16) {
            $r = $c % 16;
            $c = ($c / 16) >> 0;
            array_push($hexAr, $hex[$r]);
        }
        array_push($hexAr, $hex[$c]);
        $ret = array_reverse($hexAr);
        $item = implode('', $ret);
        $item = str_pad($item, 2, '0', STR_PAD_LEFT);
        $hexColor .= $item;
    }
    return $hexColor;
}


$rand_colors = [];

//参考：https://www.douban.com/note/271987719/
$rand_colors[] = '#CCFFFF';
$rand_colors[] = '#FFFFFF';
//$rand_colors[] = '#CCCCFF';
//$rand_colors[] = '#CCFF99';
//$rand_colors[] = '#99CCCC';
//$rand_colors[] = '#FFFFCC';
//$rand_colors[] = '#66CCCC';
//$rand_colors[] = '#99CCFF';
//$rand_colors[] = '#CCCCCC';
//$rand_colors[] = '#CCFFCC';
//$rand_colors[] = '#FFCCCC';

//for($i=0; $i<10; $i++) {
//$color_num = 255 - $i*15;
//$rand_colors[] = RGBToHex($color_num, $color_num, 220);
//}
?>

<div class="layui-card">
    <div class="layui-card-body">
        <div class="layui-tab layui-tab-brief">
            <?php if($from!="list"):?>
            <ul class="layui-tab-title" id="queue_tab">
                <li data-url="<?php echo U('setting/queue/index',['switch_type'=>'online']);?>">队列开关</li>
                <li data-url="<?php echo U('setting/queue/index',['switch_type'=>'offline']);?>">已下线队列</li>
                <li data-url="<?php echo U('/setting/queue/monitor');?>" class="layui-this">队列监控</li>
            </ul>
            <?php endif;?>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-row">
                        <table border="0" class="table addTable layui-table" lay-even>
                            <tr>
                                <td colspan="5" nowrap style="width:100%;font-size: 12px;border-top: none;">
                                    筛选统计：当前总共<b class="c_blue"><?php echo $total_server_num;?></b> 台队列机; 在跑 <b class="c_blue"><?php echo $total_queue_num;?></b> 个队列任务; 共<b class="c_blue"><?php echo $total_process_num;?></b> 个进程; (<b class="c_red"><?php echo $total_red_num;?></b>个红色预警；<b class="c_orange"><?php echo $total_yellow_num;?></b>个黄色预警；)

                                    <br />
                                    筛选区域：<select class="J_filter" id="J_server_ip">
                                        <option value="">机器筛选[全部]</option>
                                        <?php foreach($server_data as $sk=>$sv):?>
                                            <option <?php echo $server_ip == $sk ? 'selected' : '';?> value="<?php echo $sk;?>"><?php echo $sk. '['.$sv.']'; ?></option>
                                        <?php endforeach;?>
                                    </select>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <select class="J_filter" id="J_queue_id">
                                        <option value="">队列筛选[全部]</option>
                                        <?php foreach($queue_data as $qk=>$qv):?>
                                            <option <?php echo $queue_id == $qk ? 'selected' : '';?> value="<?php echo $qk;?>"><?php echo $qv; ?></option>
                                        <?php endforeach;?>
                                    </select>

                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    展示维度：
                                    <label><input type="radio" name="show_type" class="J_filter" value="server_ip" <?php echo $show_type == 'server_ip' ? 'checked="checked"' : '';?> />&nbsp;按机器</label>
                                    &nbsp;&nbsp;
                                    <label><input type="radio" name="show_type" class="J_filter" value="queue" <?php echo $show_type !== 'server_ip' ? 'checked="checked"' : '';?> />&nbsp;按队列</label>

                                    <br />
                                    黄色预警：启动超过<?php echo round($yellow_start_elapsed_time/3600);?>小时没重启 / pid最后update时间超过<?php echo round($yellow_update_elapsed_time/3600);?>小时 / 占用内存超过<?php echo get_mem_used($yellow_mem_used);?> / cpu使用超过<?php echo $yellow_cpu_used;?>% / 开启后无进程在跑
                                    <a href="javascript:;" class="J_alert" data-color='yellow' style="margin-left:32px;color: #0288d1">快速预览(<?php echo $total_yellow_num;?>个)>>></a>
                                    <br />
                                    红色预警：启动超过<?php echo round($red_start_elapsed_time/3600);?>小时没重启 / pid最后update时间超过<?php echo round($red_update_elapsed_time/3600);?>小时 / 占用内存超过<?php echo get_mem_used($red_mem_used);?> / cpu使用超过<?php echo $red_cpu_used;?>% / 关闭后进程仍在跑
                                    <a href="javascript:;" class="J_alert" data-color='red' style="margin-left:20px;color: #0288d1">快速预览(<?php echo $total_red_num;?>个)>>></a>


                                </td>
                            </tr>



                            <?php if($show_type == 'server_ip'):;?>
                                <!--按机器排列 start-->
                                <tr>
                                    <th width="200" style="background-color: #1D2939;color: #ffffff;">机器名</th>
                                    <th width="800" style="background-color: #1D2939;color: #ffffff;">队列详情</th>
                                </tr>

                                <?php
                                $ci = 1;
                                foreach($monitor_data as $_data):
                                    $rc = $ci % count($rand_colors);
                                    $bg_color = $rand_colors[$rc];
                                    $ci++;
                                    ?>
                                    <tr style="background:<?php echo $bg_color;?>">
                                        <th><?php echo $_data['server_ip'];?>-<?php echo $_data['hostname'];?></th>
                                        <td>当前共跑<strong class="c_blue"><?php echo $_data['queue_num'];?></strong>种队列，有<strong class="c_blue"><?php echo $_data['process_num'];?></strong>个进程 <?php if(!empty($_data['red_num']) || !empty($_data['yellow_num'])):;?><span class="c_red">(红色预警：<?php echo $_data['red_num'];?>个；黄色预警：<?php echo $_data['yellow_num'];?>个)</span>
                                            <?php endif;?>)</td>

                                    </tr>
                                    <?php foreach($_data['queue_list'] as $_qdata):?>
                                    <tr style="background:<?php echo $bg_color;?>">
                                        <td class="t_l <?php show_class(!empty($_qdata['is_red']), !empty($_qdata['is_yellow']))?>">
                                            <span class="my_sp1">队列名:</span><strong class="c_blue"><?php echo $_qdata['name'];?></strong><br>
                                            <span class="my_sp1">进程数:</span><strong class="c_blue"><?php echo $_qdata['process_num'];?></strong>
                                        </td>

                                        <td>
                                            <table class="ptable table table-bordered" style="display:none">
                                                <tr>
                                                    <!--<th>server_ip</th>-->
                                                    <!--<th>hostname</th>-->
                                                    <th>queue</th>
                                                    <th>pid</th>
                                                    <th>内存占用</th>
                                                    <th>cpu使用</th>
                                                    <th>进程检测</th>
                                                    <th>上报状态</th>
                                                </tr>
                                                <?php foreach($_qdata['pid_list'] as $_pdata):?>
                                                    <tr class="<?php show_class(!empty($_pdata['is_red']), !empty($_pdata['is_yellow']))?>" title="<?php echo !empty($_pdata['alert_msg']) ? $_pdata['alert_msg'] : "";?>">
                                                        <!--<td><?php echo $_pdata['server_ip'];?></td>-->
                                                        <!--<td><?php echo $_pdata['hostname'];?></td>-->
                                                        <td><?php echo $_pdata['process_pid'];?></td>
                                                        <td><?php echo $_pdata['file'];?></td>
                                                        <td><?php echo get_mem_used($_pdata['mem_used']);?></td>
                                                        <td><?php echo $_pdata['cpu_used'];?></td>
                                                        <td><?php echo $_pdata['process_at'];?><br/>(已过<?php echo $_pdata['process_check_elapsed'];?>)</td>
                                                        <td><?php echo $_pdata['update_at'];?><br/>(已过<?php echo $_pdata['update_elapsed'];?>)</td>
                                                    </tr>
                                                <?php endforeach;?>
                                                <tr>
                                                    <td class="t_c" colspan="6"><a href="javascript:;" data-ptid="" class="c_blue J_slide_up">-点击隐藏-</a></td>
                                                </tr>
                                            </table>
                                            <a href="javascript:;" class="c_blue J_slide_down" data-auto_slide="<?php echo (!empty($_qdata['is_red']) || !empty($_qdata['is_yellow'])) ? 1 : 0;?>">+点击展开+</a>
                                        </td>
                                    </tr>

                                <?php endforeach;?>
                                <?php endforeach;?>

                                <!--按机器排列 end-->
                            <?php elseif($show_type == 'queue'):; ?>
                                <!--按任务排列 start-->
                                <tr>
                                    <th width="200" style="background-color: #1D2939;color: #ffffff;">队列名</th>
                                    <th width="800" style="background-color: #1D2939;color: #ffffff;">队列详情</th>
                                </tr>

                                <?php
                                $ci = 1;
                                foreach($monitor_data as $_data):
                                    $rc = $ci % count($rand_colors);
                                    $bg_color = $rand_colors[$rc];
                                    $ci++;
                                    ?>

                                    <tr class="<?php show_class(!empty($_data['is_red']), !empty($_data['is_yellow']))?>" style="background:<?php echo $bg_color;?>">
                                        <th><?php echo $_data['name'];?></th>
                                        <td>当前共在 <strong class="c_blue"><?php echo $_data['server_num'];?></strong>台机器，
                                            共跑<strong class="c_blue"><?php echo $_data['process_num'];?></strong>个进程
                                            <?php if(!empty($_data['red_num']) || !empty($_data['yellow_num'])):;?><span class="c_red">(红色预警：<?php echo $_data['red_num'];?>个；黄色预警：<?php echo $_data['yellow_num'];?>个)</span>
                                            <?php endif;?>)</td>
                                    </tr>
                                    <?php foreach($_data['server_list'] as $_sdata):?>
                                    <tr style="background:<?php echo $bg_color;?>">
                                        <td class="t_l">
                                            <span class="my_sp1">server_ip:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><strong class="c_blue"><?php echo $_sdata['server_ip'];?></strong><br>
                                            <span class="my_sp1">hostname:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><strong class="c_blue"><?php echo $_sdata['hostname'];?></strong><br>
                                            <span class="my_sp1">process_num:</span><strong class="c_blue"><?php echo $_sdata['process_num'];?></strong>
                                        </td>

                                        <td>
                                            <table class="ptable table table-bordered" style="display:none">
                                                <tr>
                                                    <!--<th>server_ip</th>-->
                                                    <!--<th>hostname</th>-->
                                                    <th>pid</th>
                                                    <th>queue</th>
                                                    <th>内存占用</th>
                                                    <th>cpu使用</th>
                                                    <th>进程检测</th>
                                                    <th>上报状态</th>
                                                </tr>
                                                <?php foreach($_sdata['pid_list'] as $_pdata):?>
                                                    <tr class="<?php show_class(!empty($_pdata['is_red']), !empty($_pdata['is_yellow']))?>" title="<?php echo !empty($_pdata['alert_msg']) ? $_pdata['alert_msg'] : "";?>">
                                                        <!--<td><?php echo $_pdata['server_ip'];?></td>-->
                                                        <!--<td><?php echo $_pdata['hostname'];?></td>-->
                                                        <td><?php echo $_pdata['process_pid'];?></td>
                                                        <td><?php echo $_pdata['file'];?></td>
                                                        <td><?php echo get_mem_used($_pdata['mem_used']);?></td>
                                                        <td><?php echo $_pdata['cpu_used'];?></td>
                                                        <td><?php echo $_pdata['process_at'];?><br/>(已过<?php echo $_pdata['process_check_elapsed'];?>)</td>
                                                        <td><?php echo $_pdata['update_at'];?><br/>(已过<?php echo $_pdata['update_elapsed'];?>)</td>
                                                    </tr>
                                                <?php endforeach;?>
                                                <tr>
                                                    <td class="t_c" colspan="6"><a href="javascript:;" data-ptid="" class="c_blue J_slide_up">-点击隐藏-</a></td>
                                                </tr>
                                            </table>

                                            <a href="javascript:;" class="c_blue J_slide_down" data-auto_slide="<?php echo (!empty($_data['is_red']) || !empty($_data['is_yellow'])) ? 1 : 0;?>">+点击展开+</a>
                                        </td>
                                    </tr>

                                <?php endforeach;?>
                                <?php endforeach;?>

                                <!--按任务排列 end-->
                            <?php endif;?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .table{ width:100%;}
    .table th{ padding:3px 3px; line-height:26px; font-weight:normal; border:1px solid #ddd; #f9f9f9;}
    .table td{ line-height:26px; padding-top:8px;padding-bottom:7px; border-bottom:1px solid #eee; }
    .ptable tr:hover{ background:#E6EFF9; }
    .fr{float:right;}
    .fl{float:left;}
    .t_l{text-align:left;}
    .t_r{text-align:right;}
    .t_c{text-align:center;}
    .c_red {color:#FF0000}
    .c_orange{color:#FFcc00}
    .c_green{color:#00FF00}
    .c_blue{color:#0000FF}
    .c_gray{color:#c0c0c0}
    .my_sp1{width:200px;margin-right:10px;font-weight:bolder;}

    .ptable{width:100%}
</style>


<script>
    //筛选和展示方式
    $('.J_filter').change(function() {
        var _url = '?server_ip='+ $('#J_server_ip').val() + '&queue_id=' + $('#J_queue_id').val() + '&show_type=' + $('input[name=show_type]:checked').val();
        setTimeout(function(){
            window.location.href = _url+ '&r='+Math.random();
        }, 500);

    });

    // 展开收起 事件
    $('.J_slide_down').click(function() {
        var _self = $(this),
            _ptable = _self.siblings('.ptable');
        _self.fadeOut();
        _ptable.fadeIn('slow');
    });
    $('.J_slide_up').click(function() {
        var _self = $(this),
            _ptable = _self.parents('.ptable');
        _phref= _ptable.siblings('.J_slide_down');
        _ptable.fadeOut('');
        _phref.fadeIn('slow');
    });

    //如果是指定队列，则自动展开
    <?php if(!empty($queue_name)):?>
    $('.J_slide_down').click();

    <?php else:?>

    //如果有异常黄色或者红色预警，同样展开
    $('.J_slide_down').each(function(){
        var _auto_slide = $(this).attr('data-auto_slide');
        if(_auto_slide == 1) {
            $(this).click();
        }
    });

    <?php endif?>

    //警告预览
    $('.J_alert').click(function() {
        var _color = $(this).attr('data-color');
        layui.layer.open({
            type: 1,
            title:'异常快速预览',
            skin: 'layui-layer-rim', //加上边框
            area: ['1000px', 'auto'], //宽高
            content: $('#J_'+_color+'_alert_msg').html()
        });
        // $.colorbox({
        //     title :  '异常快速预览',
        //     html : $('#J_'+_color+'_alert_msg').html()
        // });
    });

</script>
<div style="display:none;" id="J_red_alert_msg">
<table class="table table-bordered layui-table" style="border:1px gray solid; width:100%">
    <tr>
        <td>no</td>
        <td>key</td>
        <td>alert_msg</td>
        <td>pid</td>
        <td>server_ip</td>
        <td>hostname</td>
        <td>mem_used</td>
        <td>cpu_used</td>
        <td>process_at</td>
        <td>update_at</td>
        <td>-</td>
    </tr>
    <?php foreach($red_arr as $rk=>$rv): ?>
        <tr>
            <td><?php echo ($rk+1);?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['key']) ? ($rv['key']) : ' ';?>  &nbsp;&nbsp;&nbsp;</td>
            <td class="c_red"><?php echo isset($rv['alert_msg']) ? ($rv['alert_msg']) : ' ';?>  &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['pid']) ? ($rv['pid']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['server_ip']) ? ($rv['server_ip']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['hostname']) ? ($rv['hostname']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['mem_used']) ? get_mem_used($rv['mem_used']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['cpu_used']) ? ($rv['cpu_used']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['process_at']) ? ($rv['process_at']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['update_at']) ? ($rv['update_at']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><a href="/setting/queue/monitor?queue_id=<?php echo $rv["id"];?>&server_ip=<?php echo isset($rv['server_ip']) ? ($rv['server_ip']) : ' ';?>" target="_blank">查看<a> &nbsp;&nbsp;&nbsp;</td>
        </tr>
    <?php endforeach; ?>
    <?php if(empty($red_arr)): ?>
        <tr>
            <td colspan="11" class="t_c">暂无该类异常</td>
        </tr>
    <?php endif; ?>
</table>
</div>
<div id="J_yellow_alert_msg" style="display:none">
<table class="table table-bordered layui-table" style="border:1px gray solid; width:100%">
    <tr>
        <td>no</td>
        <td>key</td>
        <td>alert_msg</td>
        <td>pid</td>
        <td>server_ip</td>
        <td>hostname</td>
        <td>mem_used</td>
        <td>cpu_used</td>
        <td>process_at</td>
        <td>update_at</td>
        <td>-</td>
    </tr>
    <?php foreach($yellow_arr as $rk=>$rv): ?>
        <tr>
            <td><?php echo ($rk+1);?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['key']) ? ($rv['key']) : ' ';?>  &nbsp;&nbsp;&nbsp;</td>
            <td class="c_orange"><?php echo isset($rv['alert_msg']) ? ($rv['alert_msg']) : ' ';?>  &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['pid']) ? ($rv['pid']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['server_ip']) ? ($rv['server_ip']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['hostname']) ? ($rv['hostname']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['mem_used']) ? get_mem_used($rv['mem_used']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['cpu_used']) ? ($rv['cpu_used']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['process_at']) ? ($rv['process_at']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php echo isset($rv['update_at']) ? ($rv['update_at']) : ' ';?> &nbsp;&nbsp;&nbsp;</td>
            <td><?php if(!isset($rv["no_start"])):?><a href="/setting/queue/monitor?queue_id=<?php echo $rv["id"];?>&server_ip=<?php echo isset($rv['server_ip']) ? ($rv['server_ip']) : ' ';?>" target="_blank">查看<a> &nbsp;&nbsp;&nbsp;<?php endif;?></td>
        </tr>
    <?php endforeach; ?>
    <?php if(empty($yellow_arr)): ?>
        <tr>
            <td colspan="11" class="t_c">暂无该类异常</td>
        </tr>
    <?php endif; ?>
</table>
</div>
<script>
    //切换
    $("#queue_tab li").on("click",function () {
        if($(this).hasClass("layui-this")){
            return false;
        }
        var url=$(this).attr("data-url");
        window.location=url;
    });
</script>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>
