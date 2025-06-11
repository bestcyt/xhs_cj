<?php
return [
    'master' => [
        'host' => 'rm-bp1ugn942r4l648k6.mysql.rds.aliyuncs.com',
        'port' => 3306,
        'username' => 'root',
        'password' => '835556264Aa!',
        'dbname' => 'xiaohongshu_kuhou',
        'charset' => 'utf8mb4'
    ],
    'slaves' => [
        [
            'host' => 'rm-bp1ugn942r4l648k6.mysql.rds.aliyuncs.com',
            'port' => 3306,
            'username' => 'root',
            'password' => '835556264Aa!',
            'dbname' => 'xiaohongshu_kuhou',
            'charset' => 'utf8mb4'
        ]
    ]
];