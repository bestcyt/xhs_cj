<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 小红书回复评论池
 * Class XiaohongshuRemarkModel
 * @package Mt\Model
 */
class XiaohongshuRemarkModel extends Model
{
    use TableTrait;
    use InstanceTrait;

    //是否删除
    const DELETE_YES = 1;
    const DELETE_NO = 2;
    const DELETE = [
        self::DELETE_YES => "是",
        self::DELETE_NO => "否",
    ];

    const STATUS_ARR = [
            '1' => '已删除',
            '2' => '正常',
        ];


    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'xiaohongshu_remark';
    }
}