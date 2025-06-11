<?php
return [
    'master' => [
        'host' => 'redis',
        'port' => 6379,
        'timeout' => 3,
        'pconnect' => false,
        'password' => '835556264Aa!',//授权密码
    ],
    'slaves' => [
        [
            'host' => 'redis',
            'port' => 6379,
            'timeout' => 3,
            'pconnect' => false,
            'password' => '835556264Aa!',//授权密码
        ]
    ]
];