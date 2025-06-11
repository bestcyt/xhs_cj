<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * 小红书账号
 * Class XiaohongshuAccountModel
 * @package Mt\Model
 */
class XiaohongshuAccountModel extends Model
{
    use TableTrait;
    use InstanceTrait;
    
    //是否点赞账号
    const DIAN_YES = 1;
    const DIAN_NO = 2;
    const DIAN = [
        self::DIAN_YES => "是",
        self::DIAN_NO => "否",
    ];

    //是否评论账号
    const PING_YES = 1;
    const PING_NO = 2;
    const PING = [
        self::PING_YES => "是",
        self::PING_NO => "否",
    ];

    //是否收藏账号
    const COLLECT_YES = 1;
    const COLLECT_NO = 2;
    const COLLECT = [
        self::COLLECT_YES => "是",
        self::COLLECT_NO => "否",
    ];

    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'xiaohongshu_account';
    }

    /**
     * 通过小红书号获取记录
     * @param $red_id
     * @return array|bool|mixed
     */
    public function getOneByRedId($red_id)
    {
        return $this->getRow([
            "red_id" => $red_id,
        ]);
    }

    /**
     * 通过加密id获取记录
     * @param $secret_id
     * @return array|bool|mixed
     */
    public function getOneBySecretId($secret_id)
    {
        return $this->getRow([
            "secret_id" => $secret_id,
        ]);
    }
}