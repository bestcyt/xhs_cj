<?php
/**
 * 异步任务处理
 */
include(dirname(__FILE__) . "/init.php");
set_time_limit(0);
ini_set("memory_limit", '-1');
$TaskQueue = \Mt\Queue\TaskQueue::getInstance();

while (true) {
    QueueMonitor::checkAlive();
    $limit = 1000;
    while ($limit--) {
        $data = $TaskQueue->consume();
        if (!$data) {
            break;
        }
        $data = unserialize($data);
        $task_id = $data["task_id"];
        $class = $data["class"];
        $method = $data["method"];
        $args = $data["args"];
        $classParams = [];
        if (is_array($class)) {
            $classBuffer = $class[0];
            if (count($class) > 1) {
                array_shift($class);
                $classParams = $class;
            }
            $class = $classBuffer;
        }
        if (method_exists($class, "getInstance")) {
            $object = $class::getInstance(...$classParams);
        } else {
            $object = new $class(...$classParams);
        }
        $methodArr = explode("|", $method);
        $result = null;
        foreach ($methodArr as $method) {
            $result = call_user_func_array([$object, $method], $args);
        }
        $TaskResultCache = \Mt\Cache\TaskResultCache::getInstance();
        $TaskResultCache->set($task_id, serialize($result));
        $TaskResultCache->set($task_id . ":handle", 1);
    }
    usleep(300000);
}
