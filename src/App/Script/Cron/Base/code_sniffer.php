<?php
/**
 * 监控cron长时间执行脚本并报警
 *
 * 每30分钟执行一次，
 */
include(dirname(dirname(__FILE__)) . "/init.php");


$result = [];
$dir = app_root_path();//右边不要带斜杆
/**
 * 设置某些无需校验的特殊文件等, return true 即是不需要校验
 */
\Mt\Lib\CodeSniffer::setFilterFunction(function ($filePath) {
    $dir = app_root_path();
    $filePath = substr(str_replace($dir, "", $filePath), 1);
    $filter_path = [
        "vendor",
        "src/Lib/Mail",
        "src/Lib/aliyun-dysms",
        "src/Logic/LogicTrait.php",
    ];
    foreach ($filter_path as $value) {
        if (strpos($filePath, $value) === 0) {
            return true;
        }
    }
    return false;
});
\Mt\Lib\CodeSniffer::check($dir, $result);

$email_content_arr = [];
foreach ($result as $v) {
    switch ($v['type']) {
        case 'file':
            $email_content_arr[] = "\n" . $v['origin'] . "\n文件期望改名为\n" . $v['expect'];
            break;
        case 'variable':
            $email_content_arr[] = "\n文件:{$v["file"]}\n" . $v['origin'] . "\n变量期望改名为\n" . $v['expect'];
            break;
        case 'script':
            $email_content_arr[] = "\n文件:{$v["file"]}\n" . $v['expect'];
            break;
        default:
            break;
    }
}

pr($email_content_arr);
if (!empty($email_content_arr)) {
    $email_content = implode("\n", $email_content_arr);
    $Mussy = \Mt\Lib\Mussy::getInstance();
    $Mussy->fatal_alert_email('检测语法规范性提醒', $email_content, '提醒');
    $Mussy->fatal_alert_feishu('检测语法规范性提醒', $email_content, '提醒');
}