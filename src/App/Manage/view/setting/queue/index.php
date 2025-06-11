<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-card-body">
        <div class="layui-tab layui-tab-brief">
            <ul class="layui-tab-title" id="queue_tab">
                <li data-url="<?php echo U('setting/queue/index',['switch_type'=>'online']);?>"  <?php if($switch_type=="online"):?>class="layui-this"<?php endif;?>>队列开关</li>
                <li data-url="<?php echo U('setting/queue/index',['switch_type'=>'offline']);?>" <?php if($switch_type=="offline"):?>class="layui-this"<?php endif;?>>已下线队列</li>
                <li data-url="<?php echo U('/setting/queue/monitor');?>">队列监控</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-row">
                        <?php if ($switch_type == 'online'): ?>
                            <div style="margin-bottom: 10px;">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" id="enable_all">全部开启</button>
                                <button class="layui-btn layui-btn-sm layui-btn-normal" id="disable_all">全部关闭</button>
                                统计:(总共<span class="c_blue"><?= $total_queue ?></span>个队列任务<span class="c_gray">[
                            运行:<span class="c_green"><?= $total_run_process ?></span>&nbsp;
                            关闭:<?= $total_halt_process ?>&nbsp;]
                        </span>;
                                合计运行<span class="c_blue"><?= $total_process ?></span>个进程)
                                <div style="float: right">
                                    <input type="button" class="layui-btn layui-btn-sm layui-btn-normal data-remote" id="add_queue_btn" data-remote-url="<?php echo U('/setting/queue/edit');?>" data-remote-title="添加队列" value="添加">
                                </div>
                            </div>
                        <?php endif; ?>
                        <table class="layui-table" lay-even>
                            <colgroup>
                                <col width="100">
                                <col>
                                <col width="230">
                                <col width="200">
                                <col width="240">
                                <col width="130">
                            </colgroup>
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>队列</th>
                                <th>队列信息</th>
                                <th>备注</th>
                                <th>启停</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if($queue_list):?>
                            <?php $n=0; foreach ($queue_list as $_queue): ?>
                                <tr id="tr_<?= $_queue['id'] ?>" data-id="<?php echo $_queue['id']; ?>">
                                    <td><?php echo ++$n ?></td>
                                    <td align="left">
                                        <?php echo $_queue['file'] ?><br/>
                                        <span class="run-info"></span>
                                        <a data-remote-url="<?php echo U('/setting/queue/monitor',['queue_id'=>$_queue['id'],'from'=>'list']);?>" data-remote-title="队列监控：<?php echo $_queue['file'] ?>" class="data-remote" data-remote-full="true" style="color:#0288d1;">运行监控</a>
                                        <?php if($_queue['set_check_alive']==false): ?>
                                            <span class="c_red" style="font-size: 12px;"><br>文件不存在或代码未调用QueueMonitor::checkAlive()，已被禁止运行</span>
                                        <?php elseif($_queue['set_use_queue']==false):?>
                                            <span class="c_red" style="font-size: 12px;"><br>文件不存在或代码未调用标准队列，已被禁止运行</span>
                                        <?php endif;?>
                                    </td>
                                    <td align="left" class="pool-counts">
                                    </td>
                                    <td align="left">
                                        <span class="c_gray"><?= $_queue['remark'] ?></span>
                                    </td>
                                    <td align="left">
                                        <?php if ($switch_type == 'online'): ?>
                                            <input type="radio" name="<?php echo $_queue['file']; ?>" class="change_queue_status checked_on"  value="1" data-id="<?php echo $_queue['id']; ?>" <?php if ($_queue['status']): ?> checked="checked"<?php endif; ?> id="<?php echo $_queue['file']; ?>_on"/>
                                            <label for="<?php echo $_queue['file']; ?>_on" class="layui-btn layui-btn-xs layui-btn-primary">开启</label>
                                            &nbsp;&nbsp;
                                            <input type="radio" name="<?php echo $_queue['file']; ?>" class="change_queue_status checked_off" value="0" data-id="<?php echo $_queue['id']; ?>" <?php if ($_queue['status'] == 0): ?> checked="checked"<?php endif; ?> id="<?php echo $_queue['file']; ?>_off"/>
                                            <label for="<?php echo $_queue['file']; ?>_off" class="layui-btn layui-btn-xs layui-btn-primary">关闭</label>
                                            &nbsp;&nbsp;
                                            <label for="<?php echo $_queue['file']; ?>_restart" class="layui-btn layui-btn-xs layui-btn-normal change_queue_status_reload" data-id="<?php echo $_queue['id']; ?>" id="<?php echo $_queue['file']; ?>_restart">重启</label>
                                        <?php else: ?>
                                            （已下线）
                                        <?php endif; ?>
                                    </td>
                                    <td align="left" data-id="<?php echo $_queue['id']; ?>">
                                        <?php if ($switch_type == 'online'): ?>
                                            <span data-target="#edit_modal" data-toggle="modal" class="layui-btn layui-btn-xs edit_queue data-remote"  data-remote-url="<?php echo U('/setting/queue/edit',['id'=>$_queue['id']]);?>" data-remote-title="编辑队列">编辑</span>
                                            <input type="button" class="layui-btn layui-btn-xs layui-btn-danger offline_queue" data-name="<?php echo $_queue['file']; ?>"  data-id="<?php echo $_queue['id']; ?>"  value="下线" />
                                        <?php else: ?>
                                            <span class="layui-btn layui-btn-xs layui-btn-danger delete_queue" data-name="<?php echo $_queue['file']; ?>" data-id="<?php echo $_queue['id']; ?>">删除</span>
                                            <input type="button" class="layui-btn layui-btn-xs layui-btn-normal online_queue"  data-name="<?php echo $_queue['file']; ?>"  data-id="<?php echo $_queue['id']; ?>"  value="上线" />
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else:?>
                                <tr><td colspan="6">暂无数据</td></tr>
                            <?php endif;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

<script>
    $(function () {
        //更改队列状态
        $('.change_queue_status').change(function(){
            var id = $(this).attr('data-id');
            var status = $(this).val();
            $.post('<?php echo U('/setting/queue/change_queue_status');?>', {id:id, status:status}, function(data){
                layui.layer.alert("更改成功");
            });
        });
        //重启
        $('.change_queue_status_reload').click(function(){
            var id = $(this).attr('data-id');
            $.post('<?php echo U('/setting/queue/change_queue_status');?>', {id:id, status:-1}, function(data){
                layui.layer.alert("重启成功",function () {
                    window.location.reload();
                });
            });
        });
        //全部关闭
        $('#disable_all').click(function(){
            layui.layer.confirm("是否全部关闭？",function () {
                var id = "<?= implode(',', array_keys($queue_list)) ?>";
                var status = 0;
                $.post('<?php echo U('/setting/queue/change_queue_status');?>', {id:id, status:status}, function(data){
                    layui.layer.alert("关闭成功",function () {
                        window.location.reload();
                    });
                });
            });
        });
        //全部开启
        $('#enable_all').click(function(){
            layui.layer.confirm("是否全部开启?",function () {
                var id = "<?= implode(',', array_keys($queue_list)) ?>";
                var status = 1;
                $.post('<?php echo U('/setting/queue/change_queue_status');?>', {id:id, status:status}, function(data){
                    layui.layer.alert("开启成功",function () {
                        window.location.reload();
                    });
                });
            });
        });

        //下线队列
        $('.offline_queue').click(function(){
            var online_queue_key = $(this).attr('data-name');
            var id = $(this).attr('data-id');
            var status = 2;
            layui.layer.confirm("确定下线队列"+online_queue_key+"？",function () {
                $.post('<?php echo U('/setting/queue/change_queue_status');?>', {id:id, status:status}, function(data){
                    layui.layer.alert("下线成功",function () {
                        $('#tr_'+id).remove();
                        layui.layer.closeAll();
                    });
                });
            });
        });

        //上线队列
        $('.online_queue').click(function(){
            var online_queue_key = $(this).attr('data-name');
            var id = $(this).attr('data-id');
            var status = 0;
            layui.layer.confirm("确定上线队列"+online_queue_key+"？上线后状态为关闭,需重新开启",function () {
                $.post('<?php echo U('/setting/queue/change_queue_status');?>', {id:id, status:status}, function(data){
                    layui.layer.alert('上线队列'+online_queue_key+'成功,状态为关闭,需重新开启',function () {
                        $('#tr_'+id).remove();
                        layui.layer.closeAll();
                    });
                });
            });
        });

        //删除队列
        $('.delete_queue').click(function(){
            var online_queue_key = $(this).attr('data-name');
            var id = $(this).attr('data-id');
            layui.layer.confirm("确定删除队列"+online_queue_key+"？一旦删除不可恢复!!!",function () {
                $.post('<?php echo U('/setting/queue/delete');?>', {id:id}, function(data){
                    layui.layer.alert('删除队列'+online_queue_key+'成功',function () {
                        $('#tr_'+id).remove();
                        layui.layer.closeAll();
                    });
                });
            });
        });

        //更新进程数量信息
        setInterval(function (){
            $.ajax({
                type: 'get',
                url:'<?php echo U('/setting/queue/get_run_info');?>',
                dataType : 'json',
                timeout : 2500,
                success : function(r){
                    for(i in r){
                        //运行状态显示
                        if(r[i]['status']==1){
                            var str = '[开]';
                            $('#tr_'+r[i]['id']).find('.run-info').html(str).addClass('c_green');
                        }else{
                            var str = '[关]';
                            $('#tr_'+r[i]['id']).find('.run-info').html(str).addClass('c_gray');;
                        }

                        //列表信息显示
                        var str = '';
                        $_ =  $('#tr_'+r[i]['id']).find('.pool-counts');
                        if(r[i]['counts'].length==0){
                            if(r[i]['status'] == 1){
                                str = '<span class="r_red" style="color:#FF0000">警告：未生成任务，如果任务数配置不是0，请重启试试</span>';
                            }else{
                                str = '已关闭';
                            }
                            $_.html(str)
                        }else{
                            str += '<table class="queue_info_table table table-bordered">';
                            str += '<tr><td>pool集群</td><td>设置</td><td>调度</td><td>激活</td></tr>';
                            for(_pool in r[i]['counts']){
                                str += '<tr>';
                                str += '<td>'+_pool+'</td>';
                                str += '<td align="center" class="task-counts-set" '+ ( r[i]['number']>40?' style="background-color:#efeed4;"':'' )  +'>'+(r[i]['number']?r[i]['number']:0)+'</td>';
                                str += '<td align="center" class="task-counts-total">'+r[i]['counts'][_pool]['total']+'</td>';
                                str += '<td align="center" class="task-counts-alive">'+r[i]['counts'][_pool]['alive']+'</td>';
                                str += '</tr>';
                            }
                            str += '</table>';

                            $_.html(str).find('tr').each(function(){
                                if(parseInt($(this).find('.task-counts-set').html()) > parseInt($(this).find('.task-counts-total').html())){
                                    $(this).find('.task-counts-total').addClass('c_red');
                                }
                            });
                        }
                    }
                }
            })
        } , 3000);
    })
</script>
    <style>
        .c_blue{color:#0000FF}
        .c_gray{color:#c0c0c0}
        .c_green{color:#00FF00}
        .c_red{color:#FF0000}
        .queue_info_table td{font-size: 12px;padding:5px!important;border-color: #000;}
    </style>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>