<?php
return [
    'save_handler' => 'redis',
    'save_path' => 'tcp://r-bp1tkng6n5suneb2q4pd.redis.rds.aliyuncs.com:6379?timeout=3&read_timeout=3&persistent=1&auth=' . urlencode('835556264Aa!'),
    'name' => 'FWSESSIONID',
    'gc_maxlifetime' => 3600 * 12,
    'cookie_lifetime' => 3600 * 12,
    'cookie_path' => '/',
//    'cookie_domain' => '',
    'cookie_secure' => false,
    'cookie_httponly' => true,
];