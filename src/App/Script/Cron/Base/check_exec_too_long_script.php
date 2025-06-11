<?php
/**
 * 监控cron长时间执行脚本并报警
 *
 * 每30分钟执行一次，
 */
include(dirname(dirname(__FILE__)) . "/init.php");
$date = date('Ymd');
$time = time();
// 特别过滤的脚本文件
$special_cmd = [

];
//今日报警
$cache_data = \Mt\Lib\Script\CronHealthMonitor::getInfoByDate($date);
if ($cache_data) {
    $cache_data = $cache_data['fatal'];
    $content = '';
    foreach ($cache_data as $k => $v) {
        $cache_data[$k] = $v ? json_decode($v, true) : [];

        $ret = [];
        $cmd = 'ps aux | grep -v "grep" | grep "' . $cache_data[$k]['cmd'] . '" | awk ' . "'{print $2}'" . ' | grep ' . $k;
        exec($cmd, $ret);

        // 脚本名对应的进程名和进程pid不存在
        if (empty($ret)) {
            continue;
        }
        // 特别过滤部分脚本
        if (in_array($cache_data[$k]['cmd'], $special_cmd)) {
            continue;
        }
        if ($time - $cache_data[$k]['start_at'] > 3600) { //大于两小时报警
            $diff_time = ceil(($time - $cache_data[$k]['start_at']) / 60);
            $content .= 'pid : ' . $k . ' script:' . $cache_data[$k]['cmd'] . ' 已经执行超过' . $diff_time . "分钟 server_ip:" . $cache_data[$k]['server_ip'] . "</br>";
        }
    }
    if ($content != '') {
        //报警
        $Mussy = \Mt\Lib\Mussy::getInstance();
        $result = $Mussy->fatal_alert_email('cron存在长时间执行脚本', $content, '警告');
        $result = $Mussy->fatal_alert_feishu('cron存在长时间执行脚本', $content, '警告');
    }
}