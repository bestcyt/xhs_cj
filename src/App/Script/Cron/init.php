<?php
/**
 * cron初始化脚本
 * 利用redis分布式锁，抢到才能执行脚本 保证只有一个脚本在运行  特别是多机器时
 * php add.php debug(如果有这个参数就可以直接执行 没有做分布式校验)
 */
if (PHP_SAPI != "cli") {
    exit("only allow in cli");
}

//设置时区
date_default_timezone_set('Asia/Chongqing');
$rootPath = dirname(dirname(dirname(dirname(__DIR__))));
$vendorPath = $rootPath . '/vendor';
require $vendorPath . '/autoload.php';
$appName = 'Script';

$envPath = $rootPath . "/env";
$app = \Fw\App::getInstance()->setCustomEnvPath($envPath);

$app
    ->init($rootPath, $appName, \Fw\App::MODE_CONSOLE);//最后一个参数代表是网页运行,区分于命令行
//设置最大可利用内存量  一般命令行运行默认2G
ini_set('memory_limit', '2048M');

$is_debug = (isset($argv[1]) && $argv[1] == 'debug') || (isset($_GET['debug']) && $_GET['debug'] == 1);

//容器是否处于下线中
if (\Mt\Lib\Docker::getInstance()->isShutdown()) {
    exit("容器处于下线中");
};

/**
 * 并发控制，多机触发，但最终只能一台执行
 */
$ignore_script = [
    $rootPath . '/src/App/Script/Cron/Base/queue_dispatch.php',//全部机器都默认要运行
    $rootPath . '/src/App/Script/Cron/Base/check_exec_too_long_script.php',
    $rootPath . '/src/App/Script/Cron/Base/queue_dispatch_progress_check.php',
];

$task_dt = date('YmdHi');
if (!in_array($argv[0], $ignore_script) && !$is_debug) {
    //抢占分布式锁,取到锁才能跑脚本
    $CronUniqueLock = \Mt\Lock\CronUniqueLock::getInstance();
    $key = md5($argv[0] . ":" . $task_dt . ":" . \Fw\App::getInstance()->getEnvironment());
    if (!$CronUniqueLock->addLock($key)) {
        exit('repeat task. You can execute with "debug" param . eg. "php ' . $rootPath . '/src/App/Script/Cron/script_name.php debug"');
    }
}

//记录日志
$logInfo = [
    'script' => $argv[0],
    'task_dt' => $task_dt,
    'pid' => getmypid(),
];
\Fw\App::getInstance()->getLogger()->info($logInfo, \Mt\Lib\LogType::CRON_EXEC);

//监控健康情况
\Mt\Lib\Script\CronHealthMonitor::getInstance();