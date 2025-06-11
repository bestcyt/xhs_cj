<?php
/**
 * 延迟队列恢复,每分钟执行一次
 */

include(dirname(dirname(__FILE__)) . "/init.php");
set_time_limit(0);

$begin_time = time();
$pool = \Fw\App::getInstance()->getEnvironment();
$QueueDelayModel = \Mt\Lib\Script\QueueDelayModel::getInstance();
$QueueDelayWakeupLock = \Mt\Lock\QueueDelayWakeupLock::getInstance();
$Docker = \Mt\Lib\Docker::getInstance();

while (true) {
    if ($Docker->isShutdown()) {
        break;
    }
    if (time() - $begin_time >= 59) {
        break;
    }
    $result = $QueueDelayModel->db()->from($QueueDelayModel->getTable())->multiWhere([
        "run_time <=" => time(),
        "run_time >=" => time() - 300,
        "pool" => $pool,
    ])->select("*")->limit(400)->fetchAll();
    if (!$result) {
        sleep(1);
        continue;
    }
    $QueueDelayModel->db()->multiWhere([
        "id IN" => array_column($result, "id"),
    ])->delete($QueueDelayModel->getTable())->exec();
    foreach ($result as $value) {
        if ($QueueDelayWakeupLock->addLock($value["id"])) {
            /**
             * @var $class \Mt\Lib\Script\Queue
             */
            $class = "\\" . $value["queue_class"];
            $tempData = json_decode($value["data"], true);
            if (!empty($tempData["machine_id"])) {
                $class::getInstance($tempData["machine_id"])->produce($tempData["data"]);
            } else {
                $class::getInstance()->produce(json_decode($value["data"], true));
            }
        }
    }
}