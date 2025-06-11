<?php
/**
 * 飞书报警
 */
include(dirname(dirname(__FILE__)) . "/init.php");
set_time_limit(0);
$AlertFeishuQueue = \Mt\Queue\Alert\AlertFeishuQueue::getInstance();
$FeishuRobot = \Mt\Lib\FeishuRobot::getInstance();

while (true) {
    QueueMonitor::checkAlive();
    $limit = 1000;
    while ($limit--) {
        $data = $AlertFeishuQueue->consume();
        if (!$data) {
            break;
        }
        //业务操作
        if ($data["time"] < (time() - 180)) {
            continue;//超过3分钟的不报警,防止积压之后无效发送
        }
        if (isProduct()) {
            $FeishuRobot->send($data["title"], $data["message"]);
        }
    }
    sleep(1);
}
