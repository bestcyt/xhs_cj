<?php
//全局常量定义

//预先判断是否定义的模式 方便在脚本中预先定义  从而更改配置路径
defined('ENVIRONMENT_DEVELOP') || define('ENVIRONMENT_DEVELOP', 'develop'); //开发测试环境
defined('ENVIRONMENT_PRE') || define('ENVIRONMENT_PRE', 'pre'); //拟真环境
defined('ENVIRONMENT_BETA') || define('ENVIRONMENT_BETA', 'beta'); //Beta环境
defined('ENVIRONMENT_RELEASE') || define('ENVIRONMENT_RELEASE', 'release'); //生产环境
defined('ENVIRONMENT_PRODUCT') || define('ENVIRONMENT_PRODUCT', 'product'); //生产环境

