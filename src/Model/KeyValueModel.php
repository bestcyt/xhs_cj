<?php

namespace Mt\Model;

use Fw\InstanceTrait;
use Fw\TableTrait;

/**
 * key-value简单配置
 * Class KeyValueModel
 * @package Mt\Model
 */
class KeyValueModel extends Model
{
    use TableTrait;
    use InstanceTrait;

    const LAIGU_TOKEN = "laigu_token";//来鼓账号token
    const LAIGU_LOGIN_STATUS = "laigu_login_status";//来鼓登录状态 1未登录  2已登录 3已过期
    const KEY = [
        self::LAIGU_TOKEN => "来鼓登录token",
        self::LAIGU_LOGIN_STATUS => "来鼓登录状态",
    ];

    //来鼓登录状态
    const LAIGU_LOGIN_STATUS_WAIT = 1;
    const LAIGU_LOGIN_STATUS_DONE = 2;
    const LAIGU_LOGIN_STATUS_EXPIRE = 3;
    const LAIGU_LOGIN_STATUS_MAP = [
        self::LAIGU_LOGIN_STATUS_WAIT => "未登录",
        self::LAIGU_LOGIN_STATUS_DONE => "已登录",
        self::LAIGU_LOGIN_STATUS_EXPIRE => "已过期",
    ];

    protected function __construct()
    {
        $this->dbGroup = $this->getMainDbGroup();
        $this->table = 'key_value';
    }

    /**
     * 获取值
     * @param $key
     * @return bool|mixed
     */
    public function getKeyValue($key)
    {
        $rows = $this->getKey($key);
        return $rows ? $rows["value"] : false;
    }

    /**
     * 获取记录
     * @param $key
     * @return array|bool|mixed
     */
    public function getKey($key)
    {
        $row = $this->getRow([
            "key" => $key,
        ]);
        if (empty($row)) {
            $id = $this->insert([
                "key" => $key,
                "create_time" => time(),
                "name" => self::KEY[$key],
            ]);
            $row = $this->getOne($id, true);
        }
        return $row;
    }

    /**
     * 设置
     * @param $key
     * @param $value
     * @return bool
     */
    public function setKey($key, $value)
    {
        $row = $this->getKey($key);
        return $this->update($row["id"], [
            "value" => $value,
        ]);
    }

}