<?php

return [
    'environment' => 'develop',
    'log_handler' => \Fw\Logger::HANDLER_FILE,
    'log_stdout' => '/dev/mtstdout',
    'log_path' => app_root_path() . '/logs', //日志目录
    'log_level' => 'debug', //需要实际打印的日志等级
    'behavior_log_path' => app_root_path() . '/logs/statistics',//行为日志的路径
    'is_debug' => true,//开启调试  邮件不发送
];