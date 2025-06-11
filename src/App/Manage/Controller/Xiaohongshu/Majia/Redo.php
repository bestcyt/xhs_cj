<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Majia;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\LaiguCustomerXhsModel;
use Mt\Model\XiaohongshuMajiaPlanModel;
use Mt\Model\XiaohongshuMajiaPlanResultModel;
use Mt\Queue\HandleQueue;

/**
 * 重试
 * Class Redo
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Redo extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $plan_id = intval($this->request->post("id"));

            $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
            $plan_row = $XiaohongshuMajiaPlanModel->getOne($plan_id);
            if (empty($plan_row) || !in_array($plan_row["status"], [3, 7])) {
                $this->error("当前状态无法重试");
            }
            $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
            $HandleQueue = HandleQueue::getInstance();
            $XiaohongshuMajiaPlanResultModel = XiaohongshuMajiaPlanResultModel::getInstance();
            if ($plan_row["status"] == 3) {
                //预查询失败
                $XiaohongshuMajiaPlanModel->update($plan_id, [
                    "query_red_id" => "",
                    "query_begin_time" => 0,
                    "status" => 1,
                    "result" => "",
                    "query_result" => "",
                ]);
                $shangjiaXhsArr = $LaiguCustomerXhsModel->getMulti(explode(",", $plan_row["shangjia_suren_account_id"]));
                $HandleQueue->produce([
                    "handle_type" => "majia_handle",
                    "note_url" => $plan_row["note_url"],
                    "data" => [
                        [
                            "handle_type" => 999,
                            "surenSecretId" => array_column($shangjiaXhsArr, "secret_id"),
                            "id" => $plan_id,
                            "comment_id" => "",
                            "ping_content" => "",
                            "dian_max" => "",
                        ]
                    ],
                ]);
            } elseif ($plan_row["status"] == 7) {
                $handle_result = $XiaohongshuMajiaPlanResultModel->getAll([
                    "plan_id" => $plan_id,
                    "status >" => 1,
                    "again_id" => 0,
                ]);
                foreach ($handle_result as $current_result) {
                    $current_result_id = $current_result["id"];
                    unset($current_result["id"]);
                    $current_result["handle_account_id"] = 0;
                    $current_result["handle_account"] = "";
                    $current_result["handle_time"] = 0;
                    $current_result["dispatch_time"] = 0;
                    $current_result["create_time"] = time();
                    $current_result["result"] = "";
                    $current_result["again_id"] = 0;
                    $current_result["status"] = 0;
                    $current_result["parent_id"] = $current_result_id;
                    $current_result["ori_id"] = $current_result["ori_id"] ?: $current_result_id;
                    $again_id = $XiaohongshuMajiaPlanResultModel->insert($current_result);
                    if (!empty($again_id)) {
                        $XiaohongshuMajiaPlanResultModel->update($current_result_id, [
                            "again_id" => $again_id,
                        ]);
                    }
                }
                $XiaohongshuMajiaPlanModel->update($plan_id, [
                    "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
                ]);
            }

            $this->success();
        }
    }
}