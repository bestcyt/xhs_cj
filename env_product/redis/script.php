<?php
return [
    'master' => [
        'host' => 'r-bp1tkng6n5suneb2q4pd.redis.rds.aliyuncs.com',
        'port' => 6379,
        'timeout' => 3,
        'pconnect' => false,
        'password' => '835556264Aa!',//授权密码
    ],
    'slaves' => [
        [
            'host' => 'r-bp1tkng6n5suneb2q4pd.redis.rds.aliyuncs.com',
            'port' => 6379,
            'timeout' => 3,
            'pconnect' => false,
            'password' => '835556264Aa!',//授权密码
        ]
    ]
];