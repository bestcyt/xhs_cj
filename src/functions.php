<?php

/**
 * 数据库报错记录日志和发邮件,从框架里面剥离出来  配合 init.php 里面的初始化操作
 * @param \Fw\Exception\DbException $e
 */
function dbErrorCallback(\Fw\Exception\DbException $e)
{
    static $err_container = [];
    $logInfo = [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'host' => isset($e->config['host']) ? $e->config['host'] : '',
        'host_ip' => isset($e->config['host_ip']) ? $e->config['host_ip'] : '',
        'port' => isset($e->config['port']) ? $e->config['port'] : '',
        'dbname' => isset($e->config['dbname']) ? $e->config['dbname'] : '',
        'rw_type' => $e->rwType,
        'pre_sql' => $e->preSql,
        'trace' => $e->getTraceAsString(),
    ];
    if ($e->sqlCode != \Fw\Db\Mysql::ERROR_CODE_DUPLICATE_ENTRY) {
        app_logger()->error($logInfo, $e->logType);
        $error = "灾难";
    } else {
        app_logger()->warn($logInfo, $e->logType);
        $error = "警告";
    }
    //同个请求的同种错误，只报一次错误
    $reqid = \Fw\Request::getInstance()->getReqId();
    if (!isset($err_container[$reqid])) {
        $err_container[$reqid] = [];
    }
    if (in_array(md5($logInfo["message"]), $err_container[$reqid])) {
        return;
    }
    $err_container[$reqid][] = md5($logInfo["message"]);
    $appName = currentAppName();
    $key = "e:" . $appName;
    $ErrorDbCounter = \Mt\Counter\Error\ErrorDbCounter::getInstance();
    $ErrorDbCounter->incr($key);
    $exception_count = (int)$ErrorDbCounter->get($key);
    $alert_count = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 80, 200, 500];
    if (in_array($exception_count, $alert_count) || $exception_count % 1000 == 0) {
        //发送报警邮件
        $Mussy = \Mt\Lib\Mussy::getInstance();
        $Mussy->fatal_alert_email("【数据库错误】" . $logInfo['message'], empty($logInfo['trace']) ? "" : $logInfo['trace'], $error);
        $Mussy->fatal_alert_feishu("【数据库错误】" . $logInfo['message'], empty($logInfo['trace']) ? "" : $logInfo['trace'], $error);
    }
}

/**
 * redis报错记录日志和发邮件,从框架里面剥离出来  配合 init.php 里面的初始化操作
 * @param \Fw\Exception\RedisException $e
 */
function redisErrorCallback(\Fw\Exception\RedisException $e)
{
    static $err_container = [];
    if (isset($e->config['password'])) {
        unset($e->config['password']);
    }
    $logInfo = [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'method' => $e->method,
//                'params' => $e->params, //默认不把参数记录到错误日志
        'config' => $e->config,
        'rw_type' => $e->rwType,
        'trace' => $e->getTraceAsString(),
    ];
    app_logger()->error($logInfo, $e->logType);

    //同个请求的同种错误，只报一次错误
    $reqid = \Fw\Request::getInstance()->getReqId();
    if (!isset($err_container[$reqid])) {
        $err_container[$reqid] = [];
    }
    if (in_array(md5($logInfo["message"]), $err_container[$reqid])) {
        return;
    }
    $err_container[$reqid][] = md5($logInfo["message"]);
    $appName = currentAppName();
    $key = "e:" . $appName;
    $ErrorRedisCounter = \Mt\Counter\Error\ErrorRedisCounter::getInstance();
    $ErrorRedisCounter->incr($key);
    $exception_count = (int)$ErrorRedisCounter->get($key);
    $alert_count = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 80, 200, 500];
    if (in_array($exception_count, $alert_count) || $exception_count % 1000 == 0) {
        //发送报警邮件
        $Mussy = \Mt\Lib\Mussy::getInstance();
        $Mussy->fatal_alert_email("【Redis错误】" . $logInfo['message'], $logInfo['trace'], "灾难");
        $Mussy->fatal_alert_feishu("【Redis错误】" . $logInfo['message'], $logInfo['trace'], "灾难");
    }
}

/**
 * memcache报错记录日志和发邮件,从框架里面剥离出来  配合 init.php 里面的初始化操作
 * @param \Fw\Exception\MemcacheException $e
 */
function memcacheErrorCallback(\Fw\Exception\MemcacheException $e)
{
    static $err_container = [];
    $logInfo = [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'method' => $e->method,
        'config' => $e->config,
        'trace' => $e->getTraceAsString(),
    ];
    app_logger()->error($logInfo, $e->logType);

    //同个请求的同种错误，只报一次错误
    $reqid = \Fw\Request::getInstance()->getReqId();
    if (!isset($err_container[$reqid])) {
        $err_container[$reqid] = [];
    }
    if (in_array(md5($logInfo["message"]), $err_container[$reqid])) {
        return;
    }
    $err_container[$reqid][] = md5($logInfo["message"]);
    $appName = currentAppName();
    $key = "e:" . $appName;
    $ErrorMemcacheCounter = \Mt\Counter\Error\ErrorMemcacheCounter::getInstance();
    $ErrorMemcacheCounter->incr($key);
    $exception_count = (int)$ErrorMemcacheCounter->get($key);
    $alert_count = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 80, 200, 500];
    if (in_array($exception_count, $alert_count) || $exception_count % 1000 == 0) {
        //发送报警邮件
        $Mussy = \Mt\Lib\Mussy::getInstance();
        $Mussy->fatal_alert_email("【Memcache错误】" . $logInfo['message'], $logInfo['trace'], "灾难");
        $Mussy->fatal_alert_feishu("【Memcache错误】" . $logInfo['message'], $logInfo['trace'], "灾难");
    }
}

/**
 * @param \Exception $e
 */
function phpErrorCallback($e)
{
    static $err_container = [];
    if ($e instanceof \Fw\Exception\NotFoundException) {
        return;
    }

    $logInfo = [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ];
    app_logger()->error($logInfo, \Mt\Lib\LogType::PHP_ERROR);
    //同个请求的同种错误，只报一次错误
    $reqid = \Fw\Request::getInstance()->getReqId();
    if (!isset($err_container[$reqid])) {
        $err_container[$reqid] = [];
    }
    if (in_array(md5($logInfo["message"]), $err_container[$reqid])) {
        return;
    }
    $err_container[$reqid][] = md5($logInfo["message"]);
    $appName = currentAppName();
    $key = "e:" . $appName;
    $ErrorPhpCounter = \Mt\Counter\Error\ErrorPhpCounter::getInstance();
    $ErrorPhpCounter->incr($key);
    $exception_count = (int)$ErrorPhpCounter->get($key);
    $alert_count = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 80, 200, 500];
    if (in_array($exception_count, $alert_count) || $exception_count % 1000 == 0) {
        //发送报警邮件
        $Mussy = \Mt\Lib\Mussy::getInstance();
        $Mussy->fatal_alert_email("【php错误】" . $logInfo['message'], $e->getTraceAsString(), "严重");
        $Mussy->fatal_alert_feishu("【php错误】" . $logInfo['message'], $e->getTraceAsString(), "严重");
    }
}

/**
 * 跳转方法   为了弥补response->redirect的bug,当controller未存在时(分发前)报 Creating default object from empty value
 * @param $url
 */
function redirect($url)
{
    $Response = \Fw\Response::getInstance();
    if ($Response->controller) {
        $Response->redirect($url);
    } else {
        header('Location:' . $url);
    }
    exit;
}

/**
 * 统一输出
 * @param array $return_data
 * @param int $code
 * @param string $msg
 * @return string
 */
function api_return_output($return_data = array(), $code = 0, $msg = '')
{
    $Request = \Fw\Request::getInstance();
    $reqid = \Fw\Request::getInstance()->getReqId();
    //根据业务增加行为日志记录
    $log_data = array();
    $currentAppName = currentAppName();
    if ($currentAppName == "Manage") {
        $log_data["manage_id"] = \Mt\Service\Manage\ManageLoginService::getInstance()->getCurrentAccountId();
    }

    behaviorLog($log_data);
    //返回
    $return_data = array(
        "meta" => array(
            "code" => $code,
            "msg" => $msg,
            "error" => '',
            "request_id" => $reqid,
            "request_uri" => $Request->getPathInfo(),
        ),
        "response" => $return_data
    );

    //记录排障日志
    $logData = [
        "response" => $return_data["response"],
        "post" => $_POST,
        "get" => $_GET,
    ];
    $current_uri = isset($_GET["s"]) ? $_GET["s"] : "";
    if (!empty($current_uri) && !in_array($current_uri, [

        ])) {
        \Fw\App::getInstance()->getLogger()->info($logData, \Mt\Lib\LogType::API_RETURN_OUTPUT);
    }

    header('Content-type:application/json;charset=utf-8');
    return json_encode($return_data, JSON_UNESCAPED_UNICODE);
}

/**
 * 统一报错
 * @param $error_code
 * @param null $error
 * @param array $arguments_list
 * @return string
 */
function api_return_error($error_code, $error = null, $arguments_list = array())
{
    $Request = \Fw\Request::getInstance();
    $reqid = \Fw\Request::getInstance()->getReqId();
    $error = $error ?: errorLang($error_code, $arguments_list, getCurrentLanguage());
    $error = $error ?: "未知错误";

    $result = array(
        "meta" => array(
            "code" => $error_code,
            "msg" => $error,
            "error" => $error,
            "request_id" => $reqid,
            "request_uri" => $Request->getPathInfo(),
        ),
        "response" => array()
    );

    //根据业务增加一些日志
    $currentAppName = currentAppName();
    if ($currentAppName == "Manage") {
        $currentAccount = \Mt\Service\Manage\ManageLoginService::getInstance()->getCurrentAccount();
        $result["manage_id"] = $currentAccount ? $currentAccount["id"] : 0;
        $result["manage_name"] = $currentAccount ? $currentAccount["real_name"] : "";
    }
    //记录排障日志
    $logData = $result;
    $logData["post"] = $_POST;
    $logData["get"] = $_GET;
    \Fw\App::getInstance()->getLogger()->info($logData, \Mt\Lib\LogType::API_RETURN_ERROR);

    header('Content-type:application/json;charset=utf-8');
    return json_encode($result, JSON_UNESCAPED_UNICODE);
}

/**
 * 统一输出
 * @return string
 */
function api_return_status_ok()
{
    $return_data = array('result' => TRUE);
    return api_return_output($return_data);
}

if (!function_exists("behaviorLog")) {
    /**
     * 记录行为日志
     * @param array $data
     */
    function behaviorLog($data = [])
    {
        $BehaviorLogger = \Fw\BehaviorLogger::getInstance();
        //为了安全不记录密码和access_token等敏感信息
        $filterParamKey = array('password', 'access_token');
        foreach ($filterParamKey as $value) {
            if (isset($_REQUEST[$value])) {
                $BehaviorLogger->addFilterParamKey($value);
            }
        }
        //记录头部信息
        foreach ($filterParamKey as $key => $value) {
            $filterParamKey[$key] = str_replace("_", "-", strtoupper($value));
        }
        $noRecord = ['HTTP_COOKIE', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT', 'HTTP_USER_AGENT', 'HTTP_UPGRADE_INSECURE_REQUESTS', 'HTTP_CACHE_CONTROL', 'HTTP_CONNECTION', 'HTTP_HOST', 'HTTP_AUTHORIZATION'];
        foreach ($_SERVER as $key => $value) {
            if (in_array($key, $noRecord)) {
                continue;
            }
            if (strpos($key, "HTTP_") === 0) {
                $header_key = substr($key, 5);
                //不记录敏感信息
                if (!in_array($header_key, $filterParamKey)) {
                    $data[$header_key] = $value;
                }
            }
        }
        //一些全局的额外记录
        if (isset($GLOBALS['additional_statistics_log']) && $GLOBALS['additional_statistics_log']) {
            $data = array_merge($data, $GLOBALS['additional_statistics_log']);
        }

        $BehaviorLogger->write($data);
    }
}

if (!function_exists("getMainRedis")) {
    /**
     * 获取main redis实例
     * @param bool $is_pconnect 是否长连接
     * @return \Fw\Redis
     */
    function getMainRedis($is_pconnect = false)
    {
        return connectRedis('redis/main', $is_pconnect);
    }
}

if (!function_exists("getMainMemcache")) {
    /**
     * 获取main memcache实例
     * @return \Fw\Memcache
     */
    function getMainMemcache()
    {
        $memcache_server_config = \Fw\App::getInstance()->env("memcache/main");
        return \Fw\Memcache::getInstance($memcache_server_config);
    }
}

/**
 * 获取redis连接
 * @param string $redisConfig
 * @param bool $is_pconnect
 * @return \Fw\Redis
 */
function connectRedis($redisConfig = '', $is_pconnect = false)
{
    $redis_server_config = \Fw\App::getInstance()->env($redisConfig);
    if ($is_pconnect) {
        $redis_server_config['master']['pconnect'] = $is_pconnect ? true : false;
        if (isset($redis_server_config['slaves']) && $redis_server_config['slaves']) {
            foreach ($redis_server_config['slaves'] as $key => $value) {
                $redis_server_config['slaves'][$key]['pconnect'] = $is_pconnect ? true : false;
            }
        }
    }
    return \Fw\Redis::getInstance($redis_server_config);
}

function getCurrentLanguage()
{
    if (empty($GLOBALS['current_request_language'])) {
        setCurrentLanguage("zh-Hans");
    }
    return $GLOBALS['current_request_language'];
}

function setCurrentLanguage($language = 'zh-Hans')
{
    $GLOBALS['current_request_language'] = $language;
    return true;
}

if (!function_exists("lang")) {
    /**
     * 获取多语言的对应信息
     * @param $code
     * @param array $param
     * @param string $lang
     * @return mixed|string
     */
    function lang($code, $param = [], $lang = "zh-Hans")
    {
        $Lang = \Fw\Lang::getInstance();
        $Lang->setCurrentLang($lang);
        return $Lang->show($code, $param);
    }
}

if (!function_exists("errorLang")) {
    /**
     * 获取多语言的对应信息
     * @param $code
     * @param array $param
     * @param string $lang
     * @return mixed|string
     */
    function errorLang($code, $param = [], $lang = "zh-Hans")
    {
        $ErrorLang = \Fw\ErrorLang::getInstance();
        $ErrorLang->setCurrentLang($lang);
        return $ErrorLang->show($code, $param);
    }
}


if (!function_exists("U")) {
    /**
     * 构造url
     * @param $url
     * @param array $params
     * @return string
     */
    function U($url, array $params = array())
    {
        if (0 !== strpos($url, "http")) {
            if (0 !== strpos($url, "/")) {
                $url = "/" . $url;
            }
            if (!isCli()) {
                $url = getScheme() . "://" . $_SERVER['HTTP_HOST'] . $url;
            }
        }
        if ($params) {
            $url .= (strpos($url, "?") === false ? "?" : "&") . http_build_query($params);
        }
        return $url;
    }
}

/**
 * 是否开启调试模式
 * @return bool
 */
function isDebug()
{
    return \Fw\App::getInstance()->env("app.is_debug") ? true : false;
}

/**
 * 键值提前的函数
 * @param array $data
 * @param $key1
 * @param string $key2
 * @param string $merge_sign
 * @return array
 */
function preKey($data, $key1, $key2 = '', $merge_sign = '')
{
    if (empty($data)) {
        return [];
    }
    if (!$data || !$key1) {
        return array();
    }
    $return_data = array();
    if ($key2) {
        foreach ($data as $_data) {
            if ($merge_sign) {
                $return_data[$_data[$key1] . $merge_sign . $_data[$key2]] = $_data;
            } else {
                $return_data[$_data[$key1]][$_data[$key2]] = $_data;
            }
        }
    } else {
        foreach ($data as $_data) {
            $return_data[$_data[$key1]] = $_data;
        }
    }
    return $return_data;
}

/**
 * 格式化打印
 */
function pr()
{
    $args = func_get_args();
    echo "<pre>";
    foreach ($args as $var) {
        if (is_array($var) || is_object($var)) {
            print_r($var);
        } else if (null === $var) {
            echo '[null]';
        } else if (false === $var) {
            echo '[false]';
        } else if (true === $var) {
            echo '[true]';
        } else if (is_string($var)) {
            echo "['{$var}']";
        } else {
            echo "[{$var}]";
        }
    }
    echo "</pre>";
}

/**
 * 格式化打印并结束
 */
function pre()
{
    $args = func_get_args();
    call_user_func_array('pr', $args);
    exit();
}

/**
 * 分隔数字（后台填写id列表时，用此函数提取id能支持任意分隔符）
 * @param $str
 * @return array
 */
function splitNumber($str)
{
    if (!$str) {
        return array();
    }
    if (is_array($str)) {
        return $str;
    }
    return preg_match_all("/\d+/", $str, $matches) ? $matches[0] : [];
}

/**
 * 环境判断
 * @param array $env_arr
 * @return bool
 */
function envCompare(array $env_arr = [])
{
    $env = \Fw\App::getInstance()->getEnvironment();
    return in_array($env, $env_arr);
}

function isDevelop()
{
    return envCompare([ENVIRONMENT_DEVELOP]);
}

function isPre()
{
    return envCompare([ENVIRONMENT_PRE]);
}

function isBeta()
{
    return envCompare([ENVIRONMENT_BETA]);
}

function isProduct()
{
    return envCompare([ENVIRONMENT_PRODUCT, ENVIRONMENT_RELEASE]);
}

function isRelease()
{
    return envCompare([ENVIRONMENT_RELEASE, ENVIRONMENT_PRODUCT]);
}

/**
 * 记录排障日志
 * @param $msg
 * @param $type
 */
function writeLog($msg, $type)
{
    \Fw\App::getInstance()->getLogger()->debug($msg, $type);
}

/**
 * 获取客户端IP，可选转成整数
 * @param bool $toLong
 * @param bool $use_cdn_src_ip
 * @return int|null|string
 */
function getIP($toLong = false, $use_cdn_src_ip = true)
{
    //需注意此处存在被伪造的风险，使用需谨慎，所有需严格判定IP的地方，不得信赖该值
    $onLineIp = false;
    if ($use_cdn_src_ip && _getenv('HTTP_CDN_SRC_IP') && strcasecmp(_getenv('HTTP_CDN_SRC_IP'), 'unknown')) {
        $onLineIp = _getenv('HTTP_CDN_SRC_IP');
    } elseif (_getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(_getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onLineIp = _getenv('HTTP_X_FORWARDED_FOR');
    } elseif (_getenv('REMOTE_ADDR') && strcasecmp(_getenv('REMOTE_ADDR'), 'unknown')) {
        $onLineIp = _getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER ['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], 'unknown')) {
        $onLineIp = $_SERVER ['REMOTE_ADDR'];
    }

    if (!$onLineIp) {
        return "unkonw";
    }
    preg_match("/[\d\.]{7,15}/", $onLineIp, $onLineIpMatches);
    $onLineIp = $onLineIpMatches [0] ? $onLineIpMatches [0] : null;
    unset($onLineIpMatches);
    if ($toLong) {
        $onLineIp = ip2long($onLineIp);
    }
    return $onLineIp;
}

function _getenv($strName)
{
    $r = NULL;
    if (isset($_SERVER [$strName])) {
        return $_SERVER [$strName];
    } elseif (isset($_ENV [$strName])) {
        return $_ENV [$strName];
    } elseif ($r = getenv($strName)) {
        return $r;
    } elseif (function_exists('apache_getenv') && $r = apache_getenv($strName, true)) {
        return $r;
    }
    return '';
}

if (!function_exists("cookie")) {
    /**
     * cookie设置
     * @param $key
     * @param bool $value
     * @param int $expire
     * @return bool
     */
    function cookie($key, $value = false, $expire = 86400)
    {
        if (is_array($key)) {
            foreach ($key as $k => $val) {
                cookie($k, $val, $expire);
            }
        } elseif (is_string($key)) {
            if (ltrim($key, "?") == $key) {
                $cookie_key = $key;
                if (is_null($value)) {
                    setcookie($cookie_key, '', time() - 3600, '/');
                    unset($_COOKIE[$cookie_key]);
                } elseif ($value === false) {
                    return isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : false;
                } else {
                    $expire_time = $expire ? (time() + $expire) : 0;
                    setcookie($cookie_key, $value, $expire_time, '/');
                    $_COOKIE[$cookie_key] = $value;
                }
            } else {
                $cookie_key = ltrim($key, "?");
                return isset($_COOKIE[$cookie_key]) ? true : false;
            }
        } elseif (is_null($key)) {
            foreach ($_COOKIE as $item => $temp) {
                setcookie($item, '', time() - 3600, '/');
                unset($_COOKIE[$item]);
            }
        }
        return true;
    }
}

/**
 * 检测链接是否是SSL连接
 * @return bool
 */
function isSSL()
{
    if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        return true;
    }

    //Note: 注意当使用 IIS 上的 ISAPI 方式时，如果不是通过 HTTPS 协议被访问，这个值将为 off。
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }
    if (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }

    if (!empty($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) == 'https') {
        return true;
    }

    if (!empty($_SERVER['HTTP_SCHEME']) && strtolower($_SERVER['HTTP_SCHEME']) == 'https') {
        return true;
    }
    return false;
}

/**
 * 获取https或者http
 * @return string
 */
function getScheme()
{
    $scheme = 'http';
    if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME']) {
        $scheme = $_SERVER['REQUEST_SCHEME'];
    } elseif (isset($_SERVER['HTTP_SCHEME']) && $_SERVER['HTTP_SCHEME']) {
        $scheme = $_SERVER['HTTP_SCHEME'];
    }
    if (isSSL()) {
        $scheme = 'https';
    }

    return $scheme;
}

/**
 * 验证URL是否是标准格式
 * 防止xss
 * @param string $url
 * @return bool
 */
function checkURL($url)
{
    //包含xss攻击的  则返回错误
    $newUrl = xssClean($url);
    if ($newUrl != $url) {
        return false;
    }

    if (preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $url)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 过滤包含xss攻击的内容
 * @param $data
 * @return mixed|string|string[]|null
 */
function xssClean($data)
{
    // Fix &entity\n;
    $data = str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
    do {// Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    } while ($old_data !== $data);
    // we are done...
    return $data;
}

/**
 * json_encode 美化和编码兼容处理
 * @param $data
 * @param bool $slashed
 * @param bool $output_json_header
 * @return string
 */
function _json_encode($data, $slashed = false, $output_json_header = false)
{
    if ($output_json_header) {
        header("Content-type: application/json; charset=utf-8");
    }
    return json_encode_optimized($data, $slashed);
}

function json_encode_optimized($data, $slashed = true)
{
    $opts = JSON_UNESCAPED_UNICODE;
    if ($slashed) {
        $opts = $opts | JSON_UNESCAPED_SLASHES; //此参数会把http:\/\/显示为http://，效果比较漂亮
    }
    $result = json_encode($data, $opts);
    if ($result === false && $data && json_last_error() == JSON_ERROR_UTF8) {
        $result = json_encode(utf8ize($data), $opts);
    }
    return $result;
}

//兼容非utf8字符 json加密, 参考:http://cn.php.net/manual/en/function.json-last-error.php#121233
function utf8ize($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }

    return $mixed;
}

if (!function_exists("rand_string")) {
    /**
     * 产生随机字符串
     * @param int $length
     * @param bool $onlyNumber
     * @return string
     */
    function rand_string($length = 10, $onlyNumber = false)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if ($onlyNumber) {
            $chars = "0123456789";
        }
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}

if (!function_exists("isMobile")) {
    /**
     * 是否手机号码
     * @param $mobile
     * @return bool
     */
    function isMobile($mobile)
    {
        if (preg_match("/^1[23456789]\d{9}$/", $mobile)) {
            return true;
        }
        return false;
    }
}

if (!function_exists("isEmail")) {
    /**
     * 判断是否邮箱
     * @param $email
     * @return mixed
     */
    function isEmail($email)
    {
        $pattern = '/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/';
        return filter_var($email, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $pattern)));
    }
}

function gmt_iso8601($time)
{
    return str_replace('+00:00', '.000Z', gmdate('c', $time));
}

if (!function_exists("post_json_to_request")) {
    /**
     * post请求将json转成form格式，便于兼容处理
     */
    function post_json_to_request()
    {
        if (PHP_SAPI == "cli") {
            return;
        }
        $Request = \Fw\Request::getInstance();
        if (!$Request->isPost()) {
            return;
        }
        $contentType = strtolower($Request->header("Content_Type"));
        if (strpos($contentType, "application/json") !== 0) {
            return;
        }
        $data = file_get_contents('php://input');
        if (empty($data)) {
            return;
        }
        $data = @json_decode($data, true);
        if (empty($data)) {
            return;
        }
        foreach ($data as $key => $value) {
            if (is_numeric($key) || !is_string($key)) {
                continue;
            }
            $_POST[$key] = $value;
            $_REQUEST[$key] = $value;
        }
    }
}

if (!function_exists("isJsonPost")) {
    /**
     * 是否json 的post请求
     * @return bool
     */
    function isJsonPost()
    {
        $Request = \Fw\Request::getInstance();
        if (!$Request->isPost()) {
            return false;
        }
        $contentType = strtolower($Request->header("Content_Type"));
        if (strpos($contentType, "application/json") !== 0) {
            return false;
        }
        return true;
    }
}

if (!function_exists("array_sort")) {
    /**
     * 多字段二维数组，根据某字段进行排序
     * @param array $data 要排序的数组（必填）
     * @param string $field 要排序的字段（必填）
     * @param string $sort 排序规则：SORT_DESC、SORT_ASC（必填）
     * @return array
     * */
    function array_sort($data, $field, $sort)
    {
        $fields = array_column($data, $field);
        array_multisort($fields, $sort, $data);
        return $data;
    }
}


if (!function_exists("currentAppName")) {
    /**
     * 获取当前应用名称
     * @return string
     */
    function currentAppName()
    {
        return !empty($GLOBALS["current_system"]) ? $GLOBALS["current_system"] : \Fw\App::getInstance()->getAppName();
    }
}

if (!function_exists("isCli")) {
    /**
     * 是否命令行运行
     * @return bool
     */
    function isCli()
    {
        $appMode = \Fw\App::getInstance()->getAppMode();
        return $appMode == \Fw\App::MODE_CONSOLE;
    }
}

if (!function_exists("isTimestamp")) {
    /**
     * 判断字符串是否时间戳
     * @param $value
     * @return bool
     */
    function isTimestamp($value)
    {
        if (is_numeric($value) && (int)$value == $value && strlen((string)$value) == 10 && date('Y-m-d H:i:s', $value) !== false) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists("split_string")) {
    /**
     * 根据 空格 换行 tab 逗号 分割字符串成数组
     * @param $str
     * @return array
     */
    function split_string($str)
    {
        $result = [];
        $arr = split_string_single($str, PHP_EOL);
        foreach ($arr as $val) {
            $temp_arr = split_string_single($val, "\t");
            foreach ($temp_arr as $v) {
                $temp_arr2 = split_string_single($v, " ");
                foreach ($temp_arr2 as $t) {
                    $temp_arr3 = split_string_single($t, ",");
                    foreach ($temp_arr3 as $k) {
                        $result[] = $k;
                    }
                }
            }
        }
        return $result;
    }

    function split_string_single($str, $split = " ")
    {
        $arr = [];
        $temp = explode($split, trim($str));
        foreach ($temp as $value) {
            $arr[] = trim($value);
        }
        return $arr;
    }
}

if (!function_exists("encrypt_password")) {

    /**
     * 密码加密
     * @param $password
     * @param $salt
     * @return string
     */
    function encrypt_password($password = '', $salt = '')
    {
        return md5(md5($password) . $salt);
    }
}

if (!function_exists("isInMobile")) {
    /**
     * 是否在手机中打开
     * @return bool
     */
    function isInMobile()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $pattern = "/(Mobile|iPhone|iPod|Android|Windows Phone|BlackBerry)/i";

        if (preg_match($pattern, $userAgent)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists("randArray")) {
    /**
     * 数组随机抽取几个元素
     * @param $arr
     * @param $number
     * @return array
     */
    function randArray($arr, $number): array
    {
        shuffle($arr);
        $result = array_slice($arr, 0, $number);
        return $result;
    }
}

if (!function_exists("get_pre_date")) {
    // 日期范围生成
    function get_pre_date($startDate, $days = 15) {
        $date = new DateTime($startDate);
        $date->modify('-' . ($days - 1) . ' days'); // 计算开始日期（包含起始日）
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $date->format('Y-m-d');
            $date->modify('+1 day');
        }
        return $dates;
    }
}

if (!function_exists("getChromePluginVersion")) {
    /**
     * 获取谷歌浏览器插件版本号
     * @return mixed
     */
    function getChromePluginVersion()
    {
        $file = app_root_path() . "/chrome_plugin/manifest.json";
        $result = file_get_contents($file);
        $config = json_decode($result, true);
        return $config['version'];
    }
}