<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 点赞回复马甲计划
 * Class XiaohongshuMajiaPlanModel
 * @package Mt\Model
 */
class XiaohongshuMajiaPlanModel extends Model
{
    use TableTrait;
    use InstanceTrait;

//    点赞上限
    const SUREN_DIAN_UP_LIMIT = 16;
    //    点赞上限
    const SHANGJIA_DIAN_UP_LIMIT = 8;
//    评论上限
    const PING_UP_LIMIT = 10;

    //    帖子点赞上限
    const POST_DIAN_UP_LIMIT = 100;
    //    帖子收藏上限
    const POST_COLLECT_UP_LIMIT = 100;
    //    帖子评论上限
    const POST_PING_UP_LIMIT = 100;

    //状态
    const STATUS_WAIT = 1;
    const STATUS_QUERY_DONE = 2;
    const STATUS_QUERY_FAIL = 3;
    const STATUS_RUN_WAIT = 4;
    const STATUS_RUN_ING = 5;
    const STATUS_RUN_DONE_SUCCESS = 6;
    const STATUS_RUN_DONE_FAIL = 7;
    const STATUS = [
        self::STATUS_WAIT => "待处理",
        self::STATUS_QUERY_DONE => "预查询结束",
        self::STATUS_QUERY_FAIL => "预查询失败",
        self::STATUS_RUN_WAIT => "待执行",
        self::STATUS_RUN_ING => "执行中",
        self::STATUS_RUN_DONE_SUCCESS => "结束(成功)",
        self::STATUS_RUN_DONE_FAIL => "结束(异常)",
    ];

    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'xiaohongshu_majia_plan';
    }
}