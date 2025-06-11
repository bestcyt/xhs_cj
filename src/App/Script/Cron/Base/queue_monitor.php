<?php
/**
 * 队列简单监测
 * 每分钟检测一次
 */
include(dirname(dirname(__FILE__)) . "/init.php");
set_time_limit(0);

$QueueDispatchModel = \Mt\Lib\Script\QueueDispatchModel::getInstance();
$Docker = \Mt\Lib\Docker::getInstance();
$current_t_i = intval(date("i"));

while (intval(date('i')) == $current_t_i) {
    if ($Docker->isShutdown()) {
        break;
    }
    $activeQueue = $QueueDispatchModel->load([
        "alive_at >" => 0,
    ]);
    if (empty($activeQueue)) {
        sleep(10);
        continue;
    }
    $content = "";

    //半小时检测一次
    if ($current_t_i == 0 || $current_t_i == 30) {
        //队列超过3分钟未上报进程 checkAlive
        foreach ($activeQueue as $value) {
            //3分钟未上报
            $run_time = time() - $value["alive_at"];
            if ($run_time >= 210) {
                $content .= "<tr><td>" . $value["file"] . "</td><td>" . date("Y-m-d H:i:s", $value["alive_at"]) . "</td><td>" . $run_time . "</td></tr>";
            }
        }
        if (!empty($content)) {
            $content = "超过3分钟未上报进程情况队列<table><tr><th>队列文件</th><th>最后checkAlive时间</th><th>未上报时长</th></tr>{$content}</table>建议修改循环limit或者优化代码逻辑";
        }
        //检测cpu和内存使用
        $mem_cpu = "";
        foreach ($activeQueue as $value) {
            $alert = false;
            //大于100M的报警
            $mem_used = round($value["mem_used"] / 1024 / 1024, 2);
            $max_mem_allow_use = $value["file"] == "task_handle" ? 100 : 50;
            if ($mem_used > $max_mem_allow_use) {
                $alert = true;
            }
            //cpu 占用大于30%的报警
            if ($value["cpu_used"] > 30) {
                $alert = true;
            }
            if ($alert) {
                $mem_cpu .= "<tr><td>" . $value["file"] . "</td><td>" . $mem_used . "</td><td>" . $value["cpu_used"] . "</td></tr>";
            }
        }
        if (!empty($mem_cpu)) {
            $content .= "\n内存超过50M或cpu超过30%的情况<table><tr><th>队列文件</th><th>内存占用M</th><th>cpu使用%</th></tr>{$mem_cpu}</table>全部情况可后台 队列管理-队列监控 查看";
        }
    }

    //队列被意外kill掉 需要重新分配
    $progress_content = "";
    $current_time = time();
    $expire_second = 60;
    foreach ($activeQueue as $value) {
        $lastTime = $value["process_alive_at"];
        if ($lastTime && $current_time - $lastTime > $expire_second) {
            //加上更新条件,防止实际已经下线了还更新,误报
            $r = $QueueDispatchModel->db()->multiWhere([
                "id" => $value["id"],
                "alive_at >" => 0,
                "process_alive_at <" => time() - $expire_second,
            ])->update($QueueDispatchModel->getTable(), [
                'machine' => '',
                'alive_at' => 0,
                'process_pid' => 0,
                'mem_used' => 0,
                'cpu_used' => 0,
                'server_ip' => '',
                'hostname' => '',
            ]);
            if ($r && $QueueDispatchModel->db()->affectedRows()) {
                $progress_content .= "<tr><td>" . $value["file"] . "</td><td>" . date("Y-m-d H:i:s", $lastTime) . "</td></tr>";
            }
        }
    }
    if (!empty($progress_content)) {
        $content .= "\nps检测未存活的脚本<table><tr><th>队列文件</th><th>最后ps检测存活时间</th></tr>{$progress_content}</table>可能的原因是容器下线或其他原因被强制kill掉,已更改状态待包工头启动";
    }

    if (!empty($content)) {
        $Mussy = \Mt\Lib\Mussy::getInstance();
        $Mussy->fatal_alert_email("队列监控", $content, "警告");
        $Mussy->fatal_alert_feishu("队列监控", $content, "警告");
    }
    sleep(5);
}