<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Remark;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Model\XiaohongshuRemarkModel;

/**
 * 回复话术列表
 * Class Index
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Index extends BaseController
{
    public function main()
    {
        $admin_id = intval($this->request->get("admin_id"));
        $status = intval($this->request->get("status"));
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 100;
        $where = [];

        if ($status) {
            $where["is_delete"] = $status;
        }

        if (!empty($admin_id)) {
            $where["admin_id"] = $admin_id;
        }
        $XiaohongshuRemarkModel = XiaohongshuRemarkModel::getInstance();

        $data = $XiaohongshuRemarkModel->getPageList($where, $page, $count, "id desc");
        if (!empty($data)) {
            $ManageAccountModel = ManageAccountModel::getInstance();
            $admin_arr = $ManageAccountModel->getMulti(array_column($data, "admin_id"));
            $admin_arr = preKey($admin_arr, "id");

            $status_arr = XiaohongshuRemarkModel::STATUS_ARR;

            foreach ($data as $key => $value) {
                $admin_info = $admin_arr[$value["admin_id"]] ?? [];
                $data[$key]["admin_name"] = $admin_info["real_name"] ?? "";
                $data[$key]["status_name"] = $status_arr[$value['is_delete']] ?? '';
            }
        }
        $total = $XiaohongshuRemarkModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    protected function view()
    {
        $status_arr = XiaohongshuRemarkModel::STATUS_ARR;
        $ManageAccountModel = ManageAccountModel::getInstance();
        $admin_arr = $ManageAccountModel->getAll([]);
        View::getInstance()
            ->assign("status_arr", $status_arr)
            ->assign("admin_arr", $admin_arr)
            ->render();
    }
}