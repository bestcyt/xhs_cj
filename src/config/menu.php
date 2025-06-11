<?php
return [
    [
        "flag" => "setting:rights:home",
        "name" => "权限管理",
        "icon" => "layui-icon-group",
        "url" => "",
        "controller_menus" => [
            [
                "url" => "/setting/account/index",
                "name" => "后台账号管理",
                "flag" => "setting:account:index",
            ],
            [
                "url" => "/setting/role/index",
                "name" => "角色管理",
                "flag" => "setting:role:role",
            ],
            [
                "url" => "/setting/rights/index",
                "name" => "权限设置",
                "flag" => "setting:rights:index",
            ],
        ],
    ],
    [
        "flag" => "setting:develop:home",
        "name" => "开发设置",
        "icon" => "layui-icon-util",
        "url" => "",
        "controller_menus" => [
            [
                "url" => "/setting/queue/index",
                "name" => "队列管理",
                "flag" => "setting:queue:index",
            ],
            [
                "url" => "/setting/queue/cron",
                "name" => "定时任务分析",
                "flag" => "setting:queue:cron",
            ],
        ],
    ],
    [
        "flag" => "customer:home",
        "name" => "商家信息",
        "icon" => "layui-icon-website",
        "url" => "",
        "controller_menus" => [
            [
                "url" => "/customer/account",
                "name" => "商家信息",
                "flag" => "customer:account",
            ],
            
        ],
    ],
    [
        "flag" => "xiaohongshu:home",
        "name" => "小红书管理",
        "icon" => "layui-icon-fire",
        "url" => "",
        "controller_menus" => [
            [
                "url" => "/xiaohongshu/account/index",
                "name" => "账号管理",
                "flag" => "xiaohongshu:account:index",
            ],
            [
                "url" => "/xiaohongshu/majia/home",
                "name" => "马甲计划",
                "flag" => "xiaohongshu:majia:home",
                "action_menus" => [
                    [
                        "url" => "/xiaohongshu/majia/record",
                        "name" => "计划管理",
                        "flag" => "xiaohongshu:majia:record",
                    ],
                    [
                        "url" => "/xiaohongshu/remark/index",
                        "name" => "回复词库",
                        "flag" => "xiaohongshu:remark:index",
                    ]
                ],
            ],
        ],
    ],
];