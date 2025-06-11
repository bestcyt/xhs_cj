<?php

namespace Mt\App\Manage\Controller\Setting\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;

class Index extends BaseController
{
    public function main()
    {
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 100;
        $mobile = trim(strval($this->request->get("mobile")));
        $real_name = trim(strval($this->request->get("real_name")));
        $id = intval($this->request->get("id"));
        $status = intval($this->request->get("status"));
        $where = [];
        if (!empty($mobile)) {
            $where["mobile LIKE"] = "%{$mobile}%";
        }
        if (!empty($real_name)) {
            $where["real_name LIKE"] = "%{$real_name}%";
        }
        if (!empty($id)) {
            $where["id"] = $id;
        }
        if (!empty($status)) {
            $where["status"] = $status;
        }
        $ManageAccountModel = ManageAccountModel::getInstance();
        $data = $ManageAccountModel->getPageList($where, $page, $count, "id");
        if (!empty($data)) {
            $status_arr = $ManageAccountModel::STATUS;
            $root_arr = $ManageAccountModel::ROOT;
            foreach ($data as $key => $value) {
                $data[$key]["status_name"] = $status_arr[$value["status"]];
                $data[$key]["root_name"] = $root_arr[$value["is_root"]];
                $data[$key]["create_time_format"] = date("Y-m-d H:i:s", $value["create_time"]);
            }
        }
        $total = $ManageAccountModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    protected function view()
    {
        $ManageAccountModel = ManageAccountModel::getInstance();
        View::getInstance()
            ->assign("status_arr", $ManageAccountModel::STATUS)
            ->render();
    }
}