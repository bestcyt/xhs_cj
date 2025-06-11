<?php

namespace Mt\App\Manage\Controller\Customer;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\LaiguCustomerXhsModel;
use Mt\Queue\HandleQueue;

/**
 * 添加商户小红书账号
 * Class Add
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class XhsAdd extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $id = trim(strval($this->request->post("id")));
            $xhs = trim(strval($this->request->post("xhs")));
            if (empty($xhs)) {
                $this->error("小红书号不能为空");
            }
            $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
            $info = $LaiguCustomerXhsModel->getRow(['xhs' => $xhs, 'is_delete' => $LaiguCustomerXhsModel::DELETE_NO]);
            if ($info) {
                $this->error("这个账号已被添加");
            }
            // 加进
            $res = $LaiguCustomerXhsModel->insert([
                'account_id' => $id,
                'xhs' => $xhs,
                'is_delete' => $LaiguCustomerXhsModel::DELETE_NO,
                'create_time' => time(),
            ]);
            if (empty($res)) {
                $this->error('添加失败');
            }
            //下发分配任务
            $HandleQueue = HandleQueue::getInstance();
            $HandleQueue->produce([
                "handle_type" => "user_search",
                "data" => [
                    "red_id" => $xhs,
                ],
            ]);
            $this->success();
        }
    }

    protected function view()
    {
        $id = intval($this->request->get("id"));
        View::getInstance()
            ->assign("id", $id)
            ->render();
    }
}