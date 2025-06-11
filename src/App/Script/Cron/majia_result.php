<?php
/**
 * 马甲计划结果标记
 * 每五分钟执行一次
 */
include(dirname(__FILE__) . "/init.php");
$hourMinute = intval(date("Hi"));
$XiaohongshuMajiaPlanModel = \Mt\Model\XiaohongshuMajiaPlanModel::getInstance();
$XiaohongshuMajiaPlanResultModel = \Mt\Model\XiaohongshuMajiaPlanResultModel::getInstance();

$plan_result = $XiaohongshuMajiaPlanModel->getAll([
    "create_time >=" => time() - 86400 * 10,
    "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
]);
if (!empty($plan_result)) {
    $result_count = $XiaohongshuMajiaPlanResultModel->db()->from($XiaohongshuMajiaPlanResultModel->getTable())->multiWhere([
        "plan_id IN" => array_column($plan_result, "id"),
        "again_id" => 0,
    ])->select([
        "plan_id",
        "status",
        "count(*) as total"
    ])->groupBy("plan_id,status")->fetchAll();
    $result_count = preKey($result_count, "plan_id", "status");//0待执行 1成功   2失败 3异常
    $update = [];
    foreach ($plan_result as $value) {
        if (empty($result_count[$value["id"]])) {
            continue;
        }
        if (!empty($result_count[$value["id"]][0])) {
            continue;
        }
        if (!empty($result_count[$value["id"]][2]) || !empty($result_count[$value["id"]][3])) {
            $update[] = [
                "id" => $value["id"],
                "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_DONE_FAIL,
            ];
            continue;
        }
        if (!empty($result_count[$value["id"]][1])) {
            $update[] = [
                "id" => $value["id"],
                "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_DONE_SUCCESS,
            ];
            continue;
        }
    }
    if (!empty($update)) {
        $XiaohongshuMajiaPlanModel->updateBatch($update, "id");
    }
}

//计划操作领取之后，超过15分钟没有结果上报，大概率已经game over，及时释放出来，留待有缘人领取处理
$handle_result = $XiaohongshuMajiaPlanResultModel->getAll([
    "dispatch_time >=" => time() - 3600 * 12,
    "dispatch_time <=" => time() - 900,
    "status" => 0,
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
            "status" => $XiaohongshuMajiaPlanResultModel::STATUS_EX,
            "result" => "处理超时，发起重试",
            "handle_time" => time(),
        ]);
        $XiaohongshuMajiaPlanModel->update($current_result["plan_id"], [
            "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
        ]);
    }
}

//预查询超过15分钟，大概率也是凉凉，重新发起吧
$plan_arr = $XiaohongshuMajiaPlanModel->getAll([
    "create_time >=" => time() - 86400 * 10,
    "status" => $XiaohongshuMajiaPlanModel::STATUS_WAIT,
    "query_begin_time >" => 0,
    "query_begin_time <=" => time() - 900,
]);
$LaiguCustomerXhsModel = \Mt\Model\LaiguCustomerXhsModel::getInstance();
$HandleQueue = \Mt\Queue\HandleQueue::getInstance();
foreach ($plan_arr as $plan_row) {
    $plan_id = $plan_row["id"];
    $XiaohongshuMajiaPlanModel->update($plan_id, [
        "query_red_id" => "",
        "query_begin_time" => 0,
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
}

//20点 自动重跑一次当日失败或者异常的计划  4:30 跑昨日失败或者异常的计划
if (in_array($hourMinute, [2000, 430])) {
    if ($hourMinute == 2000) {
        $plan_arr = $XiaohongshuMajiaPlanModel->getAll([
            "create_time >=" => strtotime(date("Ymd", time())),
            "status IN" => [3, 7],
        ]);
    } else {
        $plan_arr = $XiaohongshuMajiaPlanModel->getAll([
            "create_time >=" => strtotime(date("Ymd", time() - 86400)),
            "create_time <" => strtotime(date("Ymd", time())),
            "status IN" => [3, 7],
        ]);
    }
    foreach ($plan_arr as $plan_row) {
        $plan_id = $plan_row["id"];
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
    }
}