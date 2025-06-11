<?php
/**
 * 队列调度中介(包工头) ，每分钟运行一次，用于定时从table中取数据，激活任务（如果内存剩余不够，则不激活）、上报活着的进程的等工作
 */
include(dirname(dirname(__FILE__)) . "/init.php");
set_time_limit(0);


$QueueDispatchModel = \Mt\Lib\Script\QueueDispatchModel::getInstance();
$Docker = \Mt\Lib\Docker::getInstance();

$current_t_i = date('i');
$current_t_Hi = date('H:i');
sleep(rand(1, 5));//减轻并发取任务对db的冲击
$await_tasks_count = 0;
$active_success_count = 0;
$check_load_spend_time = 0;

while (date('i') == $current_t_i && date('s') < 55) {  //快超过自己生命周期的，提早自动退出，减少新老进程短暂并行的情况
    //容器下线
    if ($Docker->isShutdown()) {
        break;
    }

    //取出空闲的任务
    $await_tasks = $QueueDispatchModel->getAwaitTasks(30);
    $await_tasks_count += count($await_tasks);

    //激活任务
    if ($await_tasks) {
        foreach ($await_tasks as $v) {
            if ($active_success_count % 10 == 0) {
                $_start_time = microtime(true);
                //判断当前负载，负载太高则不运行
                $load_info = [];
                $is_high_load = $QueueDispatchModel->isHighLoad(); // 这个响应比较慢，等优化
                if ($is_high_load) {
                    $Mussy = \Mt\Lib\Mussy::getInstance();
                    $Mussy->fatal_alert_email("队列未执行", "负载太高,队列未执行", "严重");
                    $Mussy->fatal_alert_feishu("队列未执行", "负载太高,队列未执行", "严重");
                    sleep(5);
                    continue;
                }
                $check_load_spend_time = $check_load_spend_time + (microtime(true) - $_start_time);
            }

            $active_result = $QueueDispatchModel->activeTask($v);
            if ($active_result) { //抢成功了才加1
                $active_success_count++;
            }
        }
    }

    sleep(3);
}

//计算一次可以启动多少量，从而得出集群总并发数
$log = [
    'await_tasks_count' => $await_tasks_count,
    'active_success_count' => $active_success_count,
    'check_load_spend_time' => $check_load_spend_time
];
\Fw\App::getInstance()->getLogger()->error($log, \Mt\Lib\LogType::QUEUE_DISPATCH);