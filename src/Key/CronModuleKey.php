<?php

namespace Mt\Key;

/**
 * cron脚本模块key前缀
 */
class CronModuleKey
{
    const MODULE_KEY = ModuleKey::CRON_MODULE;

    const FATAL_INFO = self::MODULE_KEY . "f:";//cron 脚本运行意外退出情况
    const WARNING_INFO = self::MODULE_KEY . "w:";//cron 脚本运行内存或时长警告
}