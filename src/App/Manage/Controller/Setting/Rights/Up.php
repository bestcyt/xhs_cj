<?php

namespace Mt\App\Manage\Controller\Setting\Rights;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsModel;

class Up extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $ManageRightsModel = ManageRightsModel::getInstance();
            $resource_id = intval($this->request->post("id"));
            $resource_row = $ManageRightsModel->getOne($resource_id);
            $ManageRightsModel->moveBeforeNode($resource_row);
            echo api_return_status_ok();
        }
    }
}