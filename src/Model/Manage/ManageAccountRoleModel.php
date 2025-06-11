<?php
/**
 * 管理员权限映射表
 */

namespace Mt\Model\Manage;

use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

class ManageAccountRoleModel extends Model
{
    use TableTrait {
        insert as private _insert;
    }
    use InstanceTrait;

    private function __construct()
    {
        $this->table = 'account_roles';
        $this->dbGroup = $this->getMainDbGroup();
    }

    public function deleteByRoleId($roleId)
    {
        return $this->db()->delete($this->table)
            ->where('role_id', $roleId)
            ->exec();
    }

    public function getRoleIdsByAccountId($accountId)
    {
        $roleIds = [];
        $result = $this->db()->select('role_id')->from($this->table)->where('account_id', $accountId)->fetchAll();
        if ($result) {
            $roleIds = array_column($result, 'role_id');
        }
        return $roleIds;
    }

    public function getRolesInfoByAccountId($accountId)
    {
        $ManageRoleModel = ManageRoleModel::getInstance();
        $roleTable = $ManageRoleModel->getTable();
        return $this->db()->select('t2.*')->from($this->table, 't1')
            ->join($roleTable, 't1.role_id=t2.id', 't2')
            ->where('t1.account_id', $accountId)
            ->fetchAll();
    }

    public function assignRoles($accountId, array $roleIdArr)
    {
        $db = $this->db();
        $db->beginTrans();
        if ($this->deleteByAccountId($accountId) === false) {
            $db->rollbackTrans();
            return false;
        }
        if (!$this->insertMulti($accountId, $roleIdArr)) {
            $db->rollbackTrans();
            return false;
        }
        $db->commitTrans();
        return true;
    }

    public function deleteByAccountId($accountId)
    {
        return $this->db()->delete($this->table)
            ->where('account_id', $accountId)
            ->exec();
    }

    public function insertMulti($accountId, array $roleIdArr)
    {
        if (!$roleIdArr) {
            return false;
        }
        $data = [];
        foreach ($roleIdArr as $roleId) {
            $data[] = [
                'account_id' => $accountId,
                'role_id' => $roleId
            ];
        }
        return $this->db()->insertBatch($this->table, $data)->ignore()->exec();
    }
}