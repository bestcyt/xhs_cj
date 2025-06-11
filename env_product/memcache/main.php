<?php
/**
 * memcache配置
 */
return [
    'servers' => [
        ['host' => 'memcached', 'port' => 11211, 'weight' => 0]
    ],
    'connect_timeout' => 1000, //ms
    'binary_protocol' => false,//是否使用二进制协议
];