<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\XiaohongshuAccountModel;

/**
 * 编辑
 * Class Edit
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Edit extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
            $account_id = intval($this->request->post("id"));
            $is_dian = intval($this->request->post("is_dian"));
            $is_ping = intval($this->request->post("is_ping"));
            $is_collect = intval($this->request->post("is_collect"));
            $risk_info = trim(strval($this->request->post("risk_info")));

            $account_row = $XiaohongshuAccountModel->getOne($account_id);
            if (empty($account_id) || empty($account_row)) {
                $this->error("非法操作");
            }

            $XiaohongshuAccountModel->update($account_id, [
                "is_dian" => $is_dian,
                "is_ping" => $is_ping,
                "is_collect" => $is_collect,
                "risk_info" => $risk_info,
            ]);
            $this->success();
        }
    }

    protected function view()
    {
        $id = intval($this->request->get("id"));
        $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
        $account_row = $XiaohongshuAccountModel->getOne($id);
        View::getInstance()
            ->assign("account_row", $account_row)
            ->assign("dian_arr", $XiaohongshuAccountModel::DIAN)
            ->assign("ping_arr", $XiaohongshuAccountModel::PING)
            ->assign("collect_arr", $XiaohongshuAccountModel::COLLECT)
            ->render();
    }
}