<?php

namespace Mt\App\Manage\Controller\Setting\Role;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageRoleModel;

class Index extends BaseController
{
    public function main()
    {
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 100;
        $where = [];
        $name = trim(strval($this->request->get("name")));
        if (!empty($name)) {
            $where["name LIKE"] = "%{$name}%";
        }
        $ManageRoleModel = ManageRoleModel::getInstance();
        $data = $ManageRoleModel->getPageList($where, $page, $count);
        if (!empty($data)) {
            $admin_arr = $ManageRoleModel::ADMIN;
            foreach ($data as $key => $value) {
                $data[$key]["admin_name"] = $admin_arr[$value["is_admin"]];
            }
        }
        $total = $ManageRoleModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    protected function view()
    {
        View::getInstance()->render();
    }
}