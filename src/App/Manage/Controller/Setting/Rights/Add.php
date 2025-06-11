<?php

namespace Mt\App\Manage\Controller\Setting\Rights;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRightsApiModel;
use Mt\Model\Manage\ManageRightsModel;

class Add extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $parent_id = intval($this->request->post("parent_id"));
            $system_id = intval($this->request->post("system_id"));
            $show_type = intval($this->request->post("show_type")) ?: 1;
            $flag = trim(strval($this->request->post("flag")));
            $name = trim(strval($this->request->post("name")));
            $icon = trim(strval($this->request->post("icon")));
            $remark = trim(strval($this->request->post("remark")));
            $front_url = trim(strval($this->request->post("front_url")));
            $data = array(
                "system_id" => $system_id,
                "flag" => $flag,
                "show_type" => $show_type,
                "front_url" => $front_url,
                "remark" => $remark,
                "name" => $name,
                "icon" => $icon,
            );
            //判断flag唯一
            $ManageRightsModel = ManageRightsModel::getInstance();
            $checkRow = $ManageRightsModel->getRow([
                "system_id" => $system_id,
                "flag" => $flag,
            ]);
            if (!empty($checkRow)) {
                $this->error("唯一标识符已存在，请勿重复添加");
            }
            if ($rights_id = $ManageRightsModel->addNode($system_id, $parent_id, $data)) {
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
                            "rights_id" => $rights_id,
                            "system_id" => $system_id,
                            "api_url" => $value,
                        ];
                    }
                    if (!empty($api_arr)) {
                        ManageRightsApiModel::getInstance()->insertBatch($api_arr);
                    }
                }
                $this->success();
            } else {
                $this->error("添加失败");
            }
        }
    }

    protected function view()
    {
        $ManageRightsModel = ManageRightsModel::getInstance();
        $system_id = intval($this->request->get("system_id")) ?: $ManageRightsModel::SYSTEM_MANAGE;
        $parent_id = intval($this->request->get("parent_id"));
        View::getInstance()
            ->assign("parent_id", $parent_id)
            ->assign("system_id", $system_id)
            ->render();
    }
}