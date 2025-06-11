<?php

namespace Mt\Lib\Script;

use Fw\App;
use Fw\InstanceTrait;
use Fw\Redis;
use Mt\Key\CronModuleKey;
use Mt\Lib\Mussy;

/**
 * 用于记录cli生命周期时长和内存最大用量，以及意外退出的脚本
 *
 * 用法:Mt\Lib\Script\CronHealthMonitor::getInstance();即可
 * Mt\Lib\Script\CronHealthMonitor::getInfoByDate($date) 可看到结果
 */
class CronHealthMonitor
{
    use InstanceTrait;
    private $pid = 0;
    private $start_at = 0;
    private $is_run_in_this_cli = false;

    function __construct($data = array())
    {
        if (php_sapi_name() != 'cli') {
            return;
        }
        $this->is_run_in_this_cli = true;

        $this->pid = getmypid();
        $this->start_at = microtime(true);

        $cache_key = $this->getCliHealthMonitorFatalCacheKey(date('Ymd', $this->start_at));

        $data = array(
            'start_at' => $this->start_at,
            'cmd' => implode(' ', $_SERVER['argv']),
            'server_ip' => $this->getServerIp(),
        );
        $this->redis()->hSet($cache_key, $this->pid, $this->_json_encode($data, true, false)); //假设默认fatal，__destruct()
        $this->redis()->expireAt($cache_key, intval($this->start_at) + 86400 * 10); //保留10天记录
    }

    /**
     * 获取监控情况,按天汇总,最多保留十天
     * @param $date
     * @return array
     */
    public static function getInfoByDate($date)
    {
        $redis_server_config = App::getInstance()->env("redis/script");
        $redis = Redis::getInstance($redis_server_config);
        //意外退出情况
        $fatal = $redis->hGetAll(CronModuleKey::FATAL_INFO . $date);
        //警告情况
        $warning = $redis->hGetAll(CronModuleKey::WARNING_INFO . $date);
        return [
            'fatal' => $fatal,//意外退出未结束情况
            'warn' => $warning,//内存和时长占用情况
        ];
    }

    public function redis()
    {
        $redis_server_config = App::getInstance()->env("redis/script");
        return Redis::getInstance($redis_server_config);
    }

    public function __destruct()
    {
        if ($this->is_run_in_this_cli == false) {
            return;
        }

        //能完全结束的任务，就从fatal中删除
        $cache_key = $this->getCliHealthMonitorFatalCacheKey(date('Ymd', intval($this->start_at)));
        $data = $this->redis()->hGet($cache_key, $this->pid);
        if (empty($data)) {
            return;
        }
        $this->redis()->hDel($cache_key, $this->pid);
        $data = json_decode($data, true);

        //记录执行时长最大值
        $cache_key = $this->getCliHealthMonitorWarningCacheKey(date('Ymd', intval($this->start_at)));
        $warning_data = $this->redis()->hGet($cache_key, $data['cmd']);
        $warning_data = json_decode($warning_data, true);
        $run_time = microtime(true) - $data['start_at'];
        if (empty($warning_data['max_run_time']) || $warning_data['max_run_time'] < $run_time) {
            $warning_data['max_run_time'] = $run_time;
            $warning_data['max_run_time_start_at'] = $data['start_at'];
        }
        //记录执行内存使用最大值
        $memory_usage = memory_get_peak_usage();
        if ($memory_usage / 1024 / 1024 >= 50) {
            $Mussy = Mussy::getInstance();
            $Mussy->fatal_alert_email("脚本内存超过50M", var_export([
                "cmd" => $data['cmd'],
                "memcache_used" => round($memory_usage / 1024 / 1024, 2) . "MB",
            ], true), "警告");
            $Mussy->fatal_alert_feishu("脚本内存超过50M", var_export([
                "cmd" => $data['cmd'],
                "memcache_used" => round($memory_usage / 1024 / 1024, 2) . "MB",
            ], true), "警告");
        }
        if (empty($warning_data['max_memory_usage']) || $warning_data['max_memory_usage'] < $memory_usage) {
            $warning_data['max_memory_usage'] = $memory_usage;
            $warning_data['max_memory_usage_start_at'] = $data['start_at'];
        }
        $warning_data['server_ip'] = $data['server_ip'];
        $warning_data['pid'] = $this->pid;

        $this->redis()->hSet($cache_key, $data['cmd'], $this->_json_encode($warning_data, true, false));
        $this->redis()->expireAt($cache_key, time() + 86400 * 10); //保留10天记录
    }

    function getCliHealthMonitorFatalCacheKey($date = '20161010')
    {
        $cache_key = CronModuleKey::FATAL_INFO . $date;
        return $cache_key;
    }

    function getCliHealthMonitorWarningCacheKey($date = '20161010')
    {
        $cache_key = CronModuleKey::WARNING_INFO . $date;
        return $cache_key;
    }


    function getServerIp()
    {
        $hostname = !empty($_SERVER['HOSTNAME']) ? trim($_SERVER['HOSTNAME']) : (function_exists('exec') ? trim(exec('hostname')) : '');
        $linux_hostname_ini = '/proc/sys/kernel/hostname';
        if (empty($hostname) && file_exists($linux_hostname_ini)) {
            $hostname = trim(file_get_contents($linux_hostname_ini));
        }
        $server_ip = !empty($hostname) ? gethostbyname($hostname) : '127.0.0.1';

        return $server_ip;
    }

    /**
     * json_encode 美化和编码兼容处理
     * @param $data
     * @param bool $slashed
     * @param bool $output_json_header
     * @return string
     */
    protected function _json_encode($data, $slashed = false, $output_json_header = false)
    {
        if ($output_json_header) {
            header("Content-type: application/json; charset=utf-8");
        }
        return $this->json_encode_optimized($data, $slashed);
    }

    protected function json_encode_optimized($data, $slashed = true)
    {
        $opts = JSON_UNESCAPED_UNICODE;
        if ($slashed) {
            $opts = $opts | JSON_UNESCAPED_SLASHES; //此参数会把http:\/\/显示为http://，效果比较漂亮
        }
        $result = json_encode($data, $opts);
        if ($result === false && $data && json_last_error() == JSON_ERROR_UTF8) {
            $result = json_encode($this->utf8ize($data), $opts);
        }
        return $result;
    }

    //兼容非utf8字符 json加密, 参考:http://cn.php.net/manual/en/function.json-last-error.php#121233
    protected function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
        }

        return $mixed;
    }

}
