<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Model\Manage\ManageAccountRoleModel;
use Mt\Model\Manage\ManageRoleModel;

class AssignRoles extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            if ($id <= 0) {
                $this->error("id无效");
            }
            $roleIds = $this->request->post('role_ids');
            $ManageAccountRoleModel = ManageAccountRoleModel::getInstance();
            if (!$ManageAccountRoleModel->assignRoles($id, $roleIds)) {
                $this->error("设置失败");
            }
            $this->success();
        }
    }

    protected function view()
    {
        $id = $this->request->get('id');
        $roleIds = [];
        $allRoles = [];
        $acctInfo = [];
        if ($id > 0) {
            $ManageAccountModel = ManageAccountModel::getInstance();
            $acctInfo = $ManageAccountModel->getOne($id);
            $ManageRoleModel = ManageRoleModel::getInstance();
            $allRoles = $ManageRoleModel->getAll([]);
            $ManageAccountRoleModel = ManageAccountRoleModel::getInstance();
            $roleIds = $ManageAccountRoleModel->getRoleIdsByAccountId($id);
            if ($allRoles) {
                $status_arr = $ManageRoleModel::STATUS;
                $admin_arr = $ManageRoleModel::ADMIN;
                foreach ($allRoles as $key => $value) {
                    $allRoles[$key]["status_name"] = $status_arr[$value["status"]];
                    $allRoles[$key]["admin_name"] = $admin_arr[$value["is_admin"]];
                    $allRoles[$key]["checked"] = in_array($value["id"], $roleIds);
                }
            }
        }

        View::getInstance()->assign('id', $id)
            ->assign('all_roles', $allRoles)
            ->assign('role_ids', $roleIds)
            ->assign('acct_info', $acctInfo)
            ->render();
    }
}