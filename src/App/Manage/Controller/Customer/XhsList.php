<?php

namespace Mt\App\Manage\Controller\Customer;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\LaiguCustomerXhsModel;

/**
 * 小红书列表
 * Class Index
 * @package Mt\App\Manage\Controller\Customer
 */
class XhsList extends BaseController
{
    public function main()
    {
        //商家id
        $id = intval($this->request->get("id"));

        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 100;

        $where = [
            "is_delete" => LaiguCustomerXhsModel::DELETE_NO,
        ];

        if (!empty($id)) {
            $where["account_id"] = $id;
        }

        $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
        $data = $LaiguCustomerXhsModel->getPageList($where, $page, $count);

        if (!empty($data)) {
            
        }
        $total = $LaiguCustomerXhsModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    protected function view()
    {
        $id = intval($this->request->get("id"));
        View::getInstance()
            ->assign("id", $id)
            ->render();
    }
}