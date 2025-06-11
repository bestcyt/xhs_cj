<?php
//设置时区
date_default_timezone_set('Asia/Chongqing');

$rootPath = dirname(dirname(dirname(dirname(__DIR__))));
$appName = 'Manage';
$modules = ['setting','xiaohongshu'];//设置模块(才能支持三层访问)

$vendorPath = $rootPath . '/vendor';
require $vendorPath . '/autoload.php';
$envPath = $rootPath . "/env";
$app = \Fw\App::getInstance();
$app->setModules($modules)
    ->setCustomEnvPath($envPath)
    ->beforeRoute(function (\Fw\Request $request, \Fw\App $app) {

    })->afterRoute(function (\Fw\Request $request, \Fw\App $app) {

    })->beforeDispatch(function (\Fw\Request $request, \Fw\App $app) {
        //登录校验
        \Mt\App\Manage\Plugin\Login::getInstance()->preDispatch($request, $app);
        //权限校验
        \Mt\App\Manage\Plugin\Access::getInstance()->preDispatch($request, $app);
    })->afterDispatch(function (\Fw\Request $request, \Fw\App $app) {

    })
    ->run($rootPath, $appName);

