<?php

namespace Mt\Key;

/**
 * 杂项模块key前缀(没有归属的都可以放这里)
 */
class MussyModuleKey
{
    const MODULE_KEY = ModuleKey::MUSSY_MODULE;

    const DEMO = self::MODULE_KEY . "d:";
    const PYTHON = self::MODULE_KEY . "p:";
    const IP_ADDRESS = self::MODULE_KEY . "ip:";//IP归属地缓存
    const MAJIA_PLAN_NO_COMMENT = self::MODULE_KEY . "mpnc:";
    const XHS_WEB_SESSION = self::MODULE_KEY . "xhswbsn:";
}