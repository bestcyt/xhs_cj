<?php

namespace Mt\App\Manage\Controller\Setting\Rights;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsApiModel;
use Mt\Model\Manage\ManageRightsModel;

class Edit extends BaseController
{
    public function main()
    {
        $ManageRightsModel = ManageRightsModel::getInstance();
        if ($this->request->isPost()) {
            $show_type = intval($this->request->post("show_type")) ?: 1;
            $flag = trim(strval($this->request->post("flag")));
            $name = trim(strval($this->request->post("name")));
            $icon = trim(strval($this->request->post("icon")));
            $remark = trim(strval($this->request->post("remark")));
            $front_url = trim(strval($this->request->post("front_url")));
            $resource_id = intval($this->request->post("id"));
            $data = array(
                "flag" => $flag,
                "show_type" => $show_type,
                "front_url" => $front_url,
                "remark" => $remark,
                "name" => $name,
                "icon" => $icon,
            );
            $resourceRow = $ManageRightsModel->getOne($resource_id);
            $checkRow = $ManageRightsModel->getRow([
                "system_id" => $resourceRow["system_id"],
                "flag" => $flag,
            ]);
            if (!empty($checkRow) && $checkRow["id"] != $resource_id) {
                $this->error("唯一标识符已存在，请勿重复添加");
            }
            $ManageRightsModel->update($resource_id, $data);
            $api = trim(strval($this->request->post("api")));
            if (!empty($api)) {
                $api = explode(PHP_EOL, $api);
                $api_arr = [];
                foreach ($api as $value) {
                    $value = trim($value);
                    if (empty($value)) {
                        continue;
                    }
                    $api_arr[] = [
                        "rights_id" => $resource_id,
                        "system_id" => $resourceRow["system_id"],
                        "api_url" => $value,
                    ];
                }
                if (!empty($api_arr)) {
                    $ManageRightsApiModel = ManageRightsApiModel::getInstance();
                    $ManageRightsApiModel->deleteBatch([
                        "rights_id" => $resource_id,
                    ]);
                    $ManageRightsApiModel->insertBatch($api_arr);
                }
            }
            $this->success();
        }
    }

    protected function view()
    {
        $ManageRightsModel = ManageRightsModel::getInstance();
        $resource_id = intval($this->request->get("id"));
        $resource_row = $ManageRightsModel->getOne($resource_id);
        //权限
        $api_arr = ManageRightsApiModel::getInstance()->getApi($resource_id);
        View::getInstance()
            ->assign("resource_row", $resource_row)
            ->assign("api_arr", $api_arr)
            ->render();
    }
}