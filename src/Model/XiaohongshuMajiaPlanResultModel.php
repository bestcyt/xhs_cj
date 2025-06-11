<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 点赞回复马甲计划执行结果
 * Class XiaohongshuMajiaPlanResultModel
 * @package Mt\Model
 */
class XiaohongshuMajiaPlanResultModel extends Model
{
    use TableTrait;
    use InstanceTrait;

    //状态
    const STATUS_WAIT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL = 2;
    const STATUS_EX = 3;
    const STATUS = [
        self::STATUS_WAIT => "等待中",
        self::STATUS_SUCCESS => "成功",
        self::STATUS_FAIL => "失败",
        self::STATUS_EX => "异常",
    ];

    const HANDLE_TYPE_DIAN = 1;
    const HANDLE_TYPE_PING = 2;

    const HANDLE_POST_TYPE_DIAN = 11;

    const HANDLE_POST_TYPE_PING = 12;

    const HANDLE_POST_TYPE_COLLECT = 13;

    const HANDLE_TYPE = [
        self::HANDLE_TYPE_DIAN => "点赞",
        self::HANDLE_TYPE_PING => "评论",
        self::HANDLE_POST_TYPE_DIAN => "帖子点赞",
        self::HANDLE_POST_TYPE_PING => "帖子评论",
        self::HANDLE_POST_TYPE_COLLECT => "帖子收藏",
    ];

    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'xiaohongshu_majia_plan_result';
    }
}