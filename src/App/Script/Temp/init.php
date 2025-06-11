<?php
/**
 * 临时脚本文件  测试或者临时任务等
 * 初始化脚本
 * php add.php
 */
if (PHP_SAPI != "cli") {
    exit("only allow in cli");
}
//设置时区
date_default_timezone_set('Asia/Chongqing');
$rootPath = dirname(dirname(dirname(dirname(__DIR__))));
$vendorPath = $rootPath . '/vendor';
require $vendorPath . '/autoload.php';
//设置最大可利用内存量  一般命令行运行默认2G
ini_set('memory_limit', '2048M');
$appName = 'Script';
$envPath = $rootPath . "/env";
$app = \Fw\App::getInstance()->setCustomEnvPath($envPath);
$app->init($rootPath, $appName, \Fw\App::MODE_CONSOLE);//最后一个参数代表是网页运行,区分于命令行

//容器是否处于下线中
if (\Mt\Lib\Docker::getInstance()->isShutdown()) {
    exit("容器处于下线中");
};