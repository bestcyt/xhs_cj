<?php

namespace Mt\Key;

/**
 * 错误相关模块key前缀
 */
class ErrorModuleKey
{
    const MODULE_KEY = ModuleKey::ERROR_MODULE;

    const PHP_ERROR = self::MODULE_KEY . "php:";//php报错
    const DB_ERROR = self::MODULE_KEY . "db:";//db报错
    const REDIS_ERROR = self::MODULE_KEY . "rd:";//redis错误
    const MEMCACHE_ERROR = self::MODULE_KEY . "mc:";//mc报错
}