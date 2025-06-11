<?php
/**
 * 队列初始化脚本
 */
if (PHP_SAPI != "cli") {
    exit("only allow in cli");
}

register_shutdown_function(array('QueueMonitor', 'shutdown'));//支持重复定义，放在init.php前注册的方法前, 才能优先捕获到语法异常
sleep(rand(0, 3));//多机器并发时，增加随机sleep机制，防止任务都在时钟走得快的机器上运行
//设置时区
date_default_timezone_set('Asia/Chongqing');
$rootPath = dirname(dirname(dirname(dirname(__DIR__))));
$vendorPath = $rootPath . '/vendor';
require $vendorPath . '/autoload.php';
$appName = 'Script';
$envPath = $rootPath . "/env";
$app = \Fw\App::getInstance()->setCustomEnvPath($envPath);
$app->init($rootPath, $appName, \Fw\App::MODE_CONSOLE);//最后一个参数代表是网页运行,区分于命令行
//设置最大可利用内存量  一般命令行运行默认2G
ini_set('memory_limit', '2048M');

/**
 * 示例：
 * php demo.php --queue_dispatch_id=32231
 */
$longOpt = array(
    'queue_dispatch_id:', // 这个id对应queue_dispatch表id
);
$cli_params = getopt('', $longOpt);

$pid = getmypid();
$app->getLogger()->info(['argv' => $argv, 'pid' => $pid], \Mt\Lib\LogType::QUEUE_EXECUTE);

QueueMonitor::init($pid, $cli_params);
set_exception_handler(array('QueueMonitor', '_exception'));//这边会覆盖init.php里自定义的mtException, 优先于shutdown执行

//容器是否处于下线中
if (\Mt\Lib\Docker::getInstance()->isShutdown()) {
    exit("容器处于下线中");
};

/**
 * 队列监控类
 */
class QueueMonitor
{
    static $running_at = 0;                  //进程pid
    static $is_safe_shutdown = false;                   //进程开始时间
    static $hostname = '';       //表示shutdown是类内部主动安全退出进程，还是外部代码退出进程
    static $server_ip = '';                    //所在机器hostname
    static $exception_msg = '';                   //所在机器server_ip
    static $queue_dispatch_id = 0;              //异常捕获的信息
    static $last_update_dispatch_at = 0;          //调度id,对应queue_dispatch表id
    static $auto_die_at = 0;     //最后更新调度中心时间
    static $pool = 'default';                //自动退出的时间,可以防止超长时间不退出导致的一些问题，比如oom
    static $machine = '';               //机器或容器所在池子名
    private static $pid = 0;                   //机器或容器身份名

    static function init($pid, $cli_params = [])
    {
        self::$pid = $pid;
        self::$running_at = app_time();
        self::$auto_die_at = app_time() + rand(240, 300);//简单随机一下，防止同时重启cpu彪起来，
        self::$hostname = self::_getHostname();
        self::$server_ip = self::_getServerIp();
        self::$queue_dispatch_id = intval(@$cli_params['queue_dispatch_id']);
        self::$machine = gethostname();//容器内部hostname类似"www-php-develop-test1-4181278131-2wbg9"
        $pool = \Fw\App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        self::$pool = $pool;

        //因为性能不好，所以只在第一次运行
        self::checkHighLoad();
    }

    static function _getHostname()
    {
        $hostname = !empty($_SERVER['HOSTNAME']) ? trim($_SERVER['HOSTNAME']) : (function_exists('exec') ? trim(exec('hostname')) : '');
        $linux_hostname_ini = '/proc/sys/kernel/hostname';
        if (empty($hostname) && file_exists($linux_hostname_ini)) {
            $hostname = trim(file_get_contents($linux_hostname_ini));
        }

        return $hostname;
    }

    static function _getServerIp()
    {
        $server_ip = !empty(self::$hostname) ? gethostbyname(self::$hostname) : '127.0.0.1';

        return $server_ip;
    }

    static function checkHighLoad()
    {
        //判断当前负载，负载太高则不运行
        $QueueDispatchModel = \Mt\Lib\Script\QueueDispatchModel::getInstance();
        $is_high_load = $QueueDispatchModel->isHighLoad();
        if ($is_high_load) {
            $Mussy = \Mt\Lib\Mussy::getInstance();
            $Mussy->fatal_alert_email("队列未执行", "负载太高,队列未执行", "严重");
            $Mussy->fatal_alert_feishu("队列未执行", "负载太高,队列未执行", "严重");
            self::safeDie();
        }
    }

    static function safeDie()
    {
        self::setSafeShutdownFlag(true);
        exit;
    }

    static function setSafeShutdownFlag($flag)
    {
        self::$is_safe_shutdown = $flag;
    }

    /**
     * 检查队列进程生死(开关或重启)
     */
    static function checkAlive()
    {
        if (self::$queue_dispatch_id > 0) {
            self::_checkAlive();
            return;
        }
    }

    static function _checkAlive()
    {

        //容器下线
        $Docker_model = \Mt\Lib\Docker::getInstance();
        if ($Docker_model->isShutdown()) {
            self::safeDie();
        }

        //决定是否自己了结进程，有些任务需要通过这种方式解决一些杂症 ，比如内存释放不出，redis异常断开后连接不上
        $ttl = self::_getTTl();
        if ($ttl < 0) {
            self::safeDie();
        }

        //更新调度表
        $r = self::_updateDispatchAlive();
        if (!$r) {
            self::safeDie();
        }
    }

    static function _getTTl()
    {
        $current_time = app_time();
        return self::$auto_die_at - $current_time;
    }

    /**
     * * 上报存在
     * @param int $min_interval_second 最小上报间隔，30秒内不重复上报
     * @return bool
     */
    static function _updateDispatchAlive($min_interval_second = 30)
    {
        $current_time = app_time();
        if (self::$last_update_dispatch_at > 0 && $current_time - self::$last_update_dispatch_at < $min_interval_second) {
            return true;
        }

        $is_begin_run = self::$last_update_dispatch_at ? false : true;
        self::$last_update_dispatch_at = $current_time;
        $QueueDispatchModel = \Mt\Lib\Script\QueueDispatchModel::getInstance();
        $process_info = self::_getProcessInfo();
        $r = $QueueDispatchModel->updateAliveInfoByTaskProcess(self::$queue_dispatch_id, self::$pid, self::$machine, $current_time, $process_info['mem_used'], $process_info['cpu_used'], self::$server_ip, self::$hostname, $is_begin_run);
        return $r;
    }

    static function _getProcessInfo()
    {
        $cpu_used = 0;
        $mem_used = 0;

        $pid_process_status = '/proc/' . self::$pid . '/status';
        if (function_exists('exec')) {
            //$cmd = 'ps p'. $pid. ' fu';
            //USER        PID %CPU %MEM    VSZ   RSS TTY      STAT START   TIME COMMAND
            //root       6276  0.0  0.0 352808 17188 pts/0    S+   11:41   0:00 php mem.php
            //RSS -- 进程本身的内存占用 VSZ -- 算上共享库的总占用--此处取RSS
            $cmd = 'ps p' . self::$pid . ' fuh';
            $res = exec($cmd);
            if (!empty($res)) {
                $res = preg_replace("/[\s]+/", " ", $res);
                $tmp_ps_info = explode(" ", $res);
                $cpu_used = $tmp_ps_info[2];
                $mem_used = (int)$tmp_ps_info[5] * 1024;
            }
        } else {
            $tmp_arr = file($pid_process_status);
            foreach ($tmp_arr as $tv) {
                //VmRSS:	   17240 kB
                if (strpos($tv, 'VmRSS') !== false) {
                    $mem_used = (int)str_replace(array('kB', ' ', 'VmRSS:'), '', $tv) * 1024;
                    break;
                }
            }
        }

        return compact('cpu_used', 'mem_used');
    }

    /**
     *
     * init.php声明了register_shutdown_function(array('QueueMonitor' , 'shutdown'));，程序结束时会自动调用到此方法
     */
    static function shutdown()
    {

        $QueueDispatchModel = \Mt\Lib\Script\QueueDispatchModel::getInstance();
        $QueueDispatchModel->updateDieInfoByTaskProcess(self::$queue_dispatch_id, self::$pid, self::$machine);

        $log_data = self::_getLogData();

        if (self::$is_safe_shutdown == false) {
            $log_data['env_info'] = self::_getEnv();
            $log_data['exception_info'] = self::$exception_msg;

            $last_error = error_get_last();
            if (isset($last_error['type']) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
                $log_data['error_info'] = $last_error;
            }

            //增加信息方便排查问题
            if (!empty($GLOBALS["queue_consume_info"])) {
                $log_data['queue_consume'] = $GLOBALS["queue_consume_info"];
            }

            \Fw\App::getInstance()->getLogger()->error($log_data, \Mt\Lib\LogType::QUEUE_SHUTDOWN_NOT_SAFE);

            //报警
            $Mussy = \Mt\Lib\Mussy::getInstance();
            $content = '队列异常信息：' . var_export($log_data, true) . "\n\n 更多异常信息见debug日志queue_monitor_shutdown_is_not_safe。\n";
            $Mussy->fatal_alert_email('队列非安全退出', $content, '严重');
            $Mussy->fatal_alert_feishu('队列非安全退出', $content, '严重');
        } else {
            \Fw\App::getInstance()->getLogger()->info($log_data, \Mt\Lib\LogType::QUEUE_SHUTDOWN_SAFE);
        }
    }

    static function _getLogData()
    {
        return array(
            'pid' => self::$pid,
            'server_ip' => self::$server_ip,
            'hostname' => self::$hostname,
            'running_at' => self::$running_at . '(' . date('Y-m-d H:i:s', self::$running_at) . ')',
        );
    }

    static function _getEnv()
    {
        $env_info = [
            'user' => !empty($_SERVER['USER']) ? $_SERVER['USER'] : '',
            'home' => !empty($_SERVER['HOME']) ? $_SERVER['HOME'] : '',
            'PWD' => !empty($_SERVER['PWD']) ? $_SERVER['PWD'] : '',
            'SCRIPT' => !empty($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : (!empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : ''),
            'argv' => !empty($_SERVER['argv']) ? $_SERVER['argv'] : [],
        ];

        return $env_info;
    }

    /**
     * @param \Exception $e
     */
    static function _exception($e)
    {
        self::$exception_msg = date("Y-m-d H:i:s") . ' ' . $e->__toString();
    }
}