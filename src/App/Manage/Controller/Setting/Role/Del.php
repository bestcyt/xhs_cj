<?php

namespace Mt\App\Manage\Controller\Setting\Role;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRoleModel;

class Del extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->post('id');
            if ($roleId <= 0) {
                $this->error("id无效");
            }
            $ManageRoleModel = ManageRoleModel::getInstance();
            if (!$ManageRoleModel->delete($roleId)) {
                $this->error("删除失败");
            }
            $this->success();
        }
    }
}