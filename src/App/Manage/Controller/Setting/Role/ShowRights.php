<?php

namespace Mt\App\Manage\Controller\Setting\Role;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsModel;
use Mt\Model\Manage\ManageRoleModel;

class ShowRights extends BaseController
{
    public function main()
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