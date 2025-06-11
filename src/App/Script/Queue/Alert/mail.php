<?php
/**
 * 邮件报警
 */
include(dirname(dirname(__FILE__)) . "/init.php");
set_time_limit(0);
$AlertMailQueue = \Mt\Queue\Alert\AlertMailQueue::getInstance();
$Mail = \Mt\Lib\Mail::getInstance();

while (true) {
    QueueMonitor::checkAlive();
    $limit = 1000;
    while ($limit--) {
        $data = $AlertMailQueue->consume();
        if (!$data) {
            break;
        }
        //业务操作
        if ($data["time"] < (time() - 180)) {
            continue;//超过3分钟的不报警,防止积压之后无效发送
        }
        $Mail->send_common($data["email"], $data["subject"], $data["content"]);
    }
    sleep(1);
}
