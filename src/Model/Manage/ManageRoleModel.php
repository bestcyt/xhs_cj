<?php
/**
 * 角色和权限表
 */

namespace Mt\Model\Manage;

use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

class ManageRoleModel extends Model
{
    use TableTrait {
        delete as private _delete;
    }
    use InstanceTrait;

    //状态
    const STATUS_NORMAL = 1;
    const STATUS_FORBID = 2;
    const STATUS = [
        self::STATUS_NORMAL => "启用",
        self::STATUS_FORBID => "禁用",
    ];

    //是否超管
    const ADMIN_YES = 1;
    const ADMIN_NO = 2;
    const ADMIN = [
        self::ADMIN_YES => "是",
        self::ADMIN_NO => "否",
    ];

    private function __construct()
    {
        $this->table = 'roles';
        $this->dbGroup = $this->getMainDbGroup();
    }

    public function delete($id)
    {
        //删除角色表中记录的同时，还要删掉账号角色关联表中的对应数据
        $this->db()->beginTrans();
        if ($this->_delete($id) === false) {
            $this->db()->rollbackTrans();
            return false;
        }
        $ManageAccountRoleModel = ManageAccountRoleModel::getInstance();
        if ($ManageAccountRoleModel->deleteByRoleId($id) === false) {
            $this->db()->rollbackTrans();
            return false;
        }
        $this->db()->commitTrans();
        return true;
    }

    /**
     * 获取角色的权限id数组
     * @param array $idArr
     * @param boolean $adminCheck
     * @return array
     */
    public function getAllRightsIdsByIds(array $idArr, &$adminCheck)
    {
        $rows = $this->db()->select("*")->from($this->table)->multiWhere([
            "id IN" => $idArr,
            "status" => self::STATUS_NORMAL,
        ])->fetchAll();
        $rightsIdArr = [];
        foreach ($rows as $value) {
            if ($value["is_admin"] == self::ADMIN_YES) {
                $adminCheck = true;
            }
            $rights_ids = $value["nodes"] ? explode(",", $value["nodes"]) : [];
            $rightsIdArr = array_merge($rightsIdArr, $rights_ids);
        }
        return array_filter(array_unique($rightsIdArr));
    }

}