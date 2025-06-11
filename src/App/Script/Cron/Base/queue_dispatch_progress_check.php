<?php
/**
 * 更新队列脚本的进程存在时间
 */
include(dirname(dirname(__FILE__)) . "/init.php");
set_time_limit(0);

$Docker = \Mt\Lib\Docker::getInstance();
$current_t_i = intval(date("i", time()));
$QueueDispatchModel=\Mt\Lib\Script\QueueDispatchModel::getInstance();
$unique_path = app_root_path() . "/src/App/Script";
while (intval(date('i')) == $current_t_i) {
    if ($Docker->isShutdown()) {
        break;
    }
    $handle = popen("ps aux|grep queue_dispatch_id|grep {$unique_path}|grep -v grep", "r");
    if ($handle) {
        $read = fread($handle, 2096 * 100);
        pclose($handle);

        $match = [];
        preg_match_all("/queue_dispatch_id=(\d+)/", $read, $match);
        if (!empty($match[1])) {
            $QueueDispatchModel->db()->multiWhere([
                "id IN"=>$match[1],
            ])->update($QueueDispatchModel->getTable(),[
                "process_alive_at"=>time(),
            ])->exec();
        }
    }
    sleep(2);
}