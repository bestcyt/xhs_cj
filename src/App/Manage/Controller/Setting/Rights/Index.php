<?php

namespace Mt\App\Manage\Controller\Setting\Rights;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsModel;

class Index extends BaseController
{
    public function main()
    {
        $ManageRightsModel = ManageRightsModel::getInstance();
        $system_arr = $ManageRightsModel::SYSTEM;
        $system_id = intval($this->request->get("system_id"));
        if (!$system_id) {
            $system_id = current(array_keys($system_arr));
        }
        $tree_row = $ManageRightsModel->getTree([
            "system_id" => $system_id,
        ]);

        View::getInstance()
            ->assign("system_id", $system_id)
            ->assign("system_arr", $system_arr)
            ->assign("access_rows", [$tree_row])
            ->render();
    }
}