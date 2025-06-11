<?php

namespace Mt\App\Manage\Controller\Setting\Role;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsModel;
use Mt\Model\Manage\ManageRoleModel;

class Edit extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $roleId = $this->request->post('id');
            if ($roleId <= 0) {
                $this->error("id无效");
            }
            $roleName = trim($this->request->post('name'));
            $nodeIds = $this->request->post('node_ids');
            $ManageRoleModel = ManageRoleModel::getInstance();
            $info = [
                'name' => $roleName,
                'nodes' => implode(',', $nodeIds)
            ];
            if (!$ManageRoleModel->update($roleId, $info)) {
                $this->error("编辑失败");
            }
            $this->success();
        }
    }

    protected function view()
    {
        $id = intval($this->request->get("id"));
        $ManageRoleModel = ManageRoleModel::getInstance();
        $role_row = $ManageRoleModel->getOne($id);
        $role_row["nodes"] = explode(",", $role_row["nodes"]);
        $ManageRightsModel = ManageRightsModel::getInstance();
        $tree_row = $ManageRightsModel->getTree([
            "system_id" => $ManageRightsModel::SYSTEM_MANAGE,
        ]);
        View::getInstance()
            ->assign("access_rows", [$tree_row])
            ->assign("role_row", $role_row)
            ->render();
    }
}