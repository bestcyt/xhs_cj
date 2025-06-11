<?php
/**
 * 系统权限关联api
 */

namespace Mt\Model\Manage;

use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

class ManageRightsApiModel extends Model
{
    use InstanceTrait;
    use TableTrait;

    protected function __construct()
    {
        $this->table = 'rights_api';
        $this->dbGroup = $this->getMainDbGroup();
    }

    /**
     * 获取权限关联api
     * @param $rights_id
     * @return array
     */
    public function getApi($rights_id)
    {
        $rows = $this->db()->select("api_url")->multiWhere([
            "rights_id" => $rights_id,
        ])->from($this->table)->fetchAll();
        return array_column($rows, "api_url");
    }

    public function getApiByRightsIds(array $rightsIdArr)
    {
        $rows = $this->db()->select("api_url,system_id")->multiWhere([
            "rights_id IN" => $rightsIdArr,
        ])->from($this->table)->fetchAll();
        $data = [];
        foreach ($rows as $value) {
            if (!isset($data[$value["system_id"]])) {
                $data[$value["system_id"]] = [];
            }
            $data[$value["system_id"]][] = $value["api_url"];
        }
        return $data;
    }
}