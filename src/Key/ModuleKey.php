<?php

namespace Mt\Key;

/**
 * 一级前缀键名(模块)
 */
class ModuleKey
{
    const PREFIX = 'xiaohongshu_kuhou:';//全局key前缀
    const CRON_MODULE = self::PREFIX . 'cron:';//命令行脚本
    const ERROR_MODULE = self::PREFIX . 'e:';//错误相关模块
    const MUSSY_MODULE = self::PREFIX . "m:";//杂项,没有归属的都可以放这里
    const LOCK_MODULE = self::PREFIX . 'l:';//锁相关
    const BITMAP_MODULE = self::PREFIX . 'bit:';//bitmap相关
    const ID_GENERATOR_MODULE = self::PREFIX . 'id:';//发号器相关
    const BLOOM_MODULE = self::PREFIX . 'blo:';//布隆过滤器相关
    const QUEUE_MODULE = self::PREFIX . 'q:';//队列相关
    const TASK_MODULE = self::PREFIX . 't:';//任务相关
}