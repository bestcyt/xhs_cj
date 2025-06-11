<?php

namespace Mt\App\Manage\Controller\Setting\Rights;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsApiModel;
use Mt\Model\Manage\ManageRightsModel;

class Del extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $ManageRightsModel = ManageRightsModel::getInstance();
            $resource_id = intval($this->request->post("id"));
            $resource_row = $ManageRightsModel->getOne($resource_id);
            $ManageRightsModel->deleteNode($resource_row);
            ManageRightsApiModel::getInstance()->deleteBatch([
                "rights_id" => $resource_id,
            ]);
            echo api_return_status_ok();
        }
    }
}