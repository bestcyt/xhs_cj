<?php

namespace Mt\App\Manage\Controller\Setting\Role;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsModel;
use Mt\Model\Manage\ManageRoleModel;

class Add extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $roleName = trim($this->request->post('name'));
            $nodeIds = $this->request->post('node_ids') ?: [];
            $ManageRoleModel = ManageRoleModel::getInstance();
            $info = [
                'name' => $roleName,
                'nodes' => implode(',', $nodeIds)
            ];
            if (!$ManageRoleModel->insert($info)) {
                $this->error("添加失败");
            }
            $this->success();
        }
    }

    protected function view()
    {
        $ManageRightsModel = ManageRightsModel::getInstance();
        $tree_row = $ManageRightsModel->getTree([
            "system_id" => $ManageRightsModel::SYSTEM_MANAGE,
        ]);
        View::getInstance()
            ->assign("access_rows", [$tree_row])
            ->render();
    }
}