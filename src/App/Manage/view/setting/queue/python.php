<?php \Mt\App\Manage\Helper\Layout::headerLayUi(); ?>
<div class="layui-card">
    <div class="layui-card-body">
        <div class="layui-tab layui-tab-brief" lay-filter="tableTab" style="position: relative;">
            <span class="layui-btn layui-btn-sm" style="position: absolute;right: 15px;top:5px;z-index: 100" id="restart_btn">立即重启</span>
            <ul class="layui-tab-title">
                <li class="layui-this">python常驻脚本</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    <div class="layui-row">
                        <table id="table1" class="layui-table">
                            <thead>
                            <tr>
                                <th>脚本标识</th>
                                <th>脚本名</th>
                                <th>最近心跳时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data as $value):?>
                                <tr>
                                    <td><?php echo $value["key"];?></td>
                                    <td><?php echo $value["title"];?></td>
                                    <td>
                                        <?php foreach($value['heart'] as $val):?>
                                        <?php echo $val['name'];?>：<?php echo date("Y-m-d H:i:s",$val["time"]);?><br/>
                                        <?php endforeach;?>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        $("#restart_btn").on("click",function(){
            layui.layer.confirm("确认立即重启？",function () {
                $.myAjaxPost("<?php echo U('/setting/queue/python');?>",{},{});
            });
        });
    </script>
    <style>
        .layui-table-cell {
            height:auto;
            overflow:visible;
            text-overflow:inherit;
            white-space:normal;
        }
    </style>
<?php \Mt\App\Manage\Helper\Layout::footerLayUi(); ?>