<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Majia;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\XiaohongshuMajiaPlanModel;
use Mt\Model\XiaohongshuMajiaPlanResultModel;

/**
 * 删除
 * Class Delete
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Delete extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $plan_id = intval($this->request->post("id"));

            $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
            $XiaohongshuMajiaPlanResultModel = XiaohongshuMajiaPlanResultModel::getInstance();
            $plan = $XiaohongshuMajiaPlanModel->getOne($plan_id);
            if (empty($plan) || $plan['status'] > $XiaohongshuMajiaPlanModel::STATUS_WAIT) {
                $this->error("当前计划状态为" . XiaohongshuMajiaPlanModel::STATUS[$plan['status']]);
            }

            $XiaohongshuMajiaPlanModel->delete($plan_id);
            $XiaohongshuMajiaPlanResultModel->deleteBatch(['plan_id' => $plan_id]);

            $this->success();
        }
    }
}