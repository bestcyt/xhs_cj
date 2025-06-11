<?php

include dirname(__FILE__) . "/init.php";

$PlanResultQueue = \Mt\Queue\PlanResultQueue::getInstance();
$XiaohongshuMajiaPlanModel = \Mt\Model\XiaohongshuMajiaPlanModel::getInstance();
$XiaohongshuMajiaPlanResultModel = \Mt\Model\XiaohongshuMajiaPlanResultModel::getInstance();
//评论随机
$XiaohongshuRemarkModel = \Mt\Model\XiaohongshuRemarkModel::getInstance();
$ping_content_list = $XiaohongshuRemarkModel->getAll(['is_delete' => $XiaohongshuRemarkModel::DELETE_NO]);

$plan_id = $PlanResultQueue->consume();
if (!$plan_id) {
    return;
}
$plan_row = $XiaohongshuMajiaPlanModel->getOne($plan_id);
if (empty($plan_row)) {
    return;
}
$comment_result = json_decode($plan_row["query_result"], true);
$insert_data = [];
if ($comment_result) {
    foreach ($comment_result as $comment) {
        if ($comment["status"] == 2) {
            continue;
        }
        foreach ($comment["result"] as $value) {
            $temp = [
                "plan_id" => $plan_id,
                "suren_type" => $value["type"] == "shangjia_comment" ? 2 : 1,
                "create_time" => time(),
                "suren_nick" => $value["comment_nick"],
                "suren_comment_id" => $value["comment_id"],
                "suren_ping_content" => $value["comment_content"],
                "suren_ping_date" => $value["comment_date"],
            ];
            $target_field = $temp["suren_type"] == 2 ? "shangjia_suren_dian_number" : "suren_dian_number";
            for ($i = 1; $i <= $plan_row[$target_field]; $i++) {
                $temp["handle_type"] = $XiaohongshuMajiaPlanResultModel::HANDLE_TYPE_DIAN;
                $temp["handle_content"] = "";
                $insert_data[] = $temp;
            }
            if ($temp["suren_type"] == 2) {
                for ($i = 1; $i <= $plan_row["shangjia_suren_ping_number"]; $i++) {
                    $temp["handle_type"] = $XiaohongshuMajiaPlanResultModel::HANDLE_TYPE_PING;
                    $rand_ping_content_key = array_rand($ping_content_list);
                    $ping_content = $ping_content_list[$rand_ping_content_key]['content'];
                    $temp["handle_content"] = $ping_content;
                    $insert_data[] = $temp;
                }
            }
        }
    } 
}

// 养贴点赞
if ($plan_row["post_dian_number"] > 0) {
    $temp = [
        "plan_id" => $plan_id,
        "suren_type" => 0,
        "suren_account_id" => 0,
        "suren_account" => 0,
        "suren_nick" => '',
        "create_time" => time(),
        "suren_comment_id" => 'post',
        "suren_ping_content" => '',
        "suren_ping_date" => '',
        "handle_content" => '',
    ];
    for ($i = 1; $i <= $plan_row["post_dian_number"]; $i++) {
        $temp["handle_type"] = $XiaohongshuMajiaPlanResultModel::HANDLE_POST_TYPE_DIAN;
        $insert_data[] = $temp;
    }
}
// 养贴收藏
if ($plan_row["post_collect_number"] > 0) {
    $temp = [
        "plan_id" => $plan_id,
        "suren_type" => 0,
        "suren_account_id" => 0,
        "suren_account" => 0,
        "suren_nick" => '',
        "create_time" => time(),
        "suren_comment_id" => 'post',
        "suren_ping_content" => '',
        "suren_ping_date" => '',
        "handle_content" => '',
    ];
    for ($i = 1; $i <= $plan_row["post_collect_number"]; $i++) {
        $temp["handle_type"] = $XiaohongshuMajiaPlanResultModel::HANDLE_POST_TYPE_COLLECT;
        $insert_data[] = $temp;
    }
}
// 养贴评论
if ($plan_row["post_ping_number"] > 0) {
    $temp = [
        "plan_id" => $plan_id,
        "suren_type" => 0,
        "suren_account_id" => 0,
        "suren_account" => 0,
        "suren_nick" => '',
        "create_time" => time(),
        "suren_comment_id" => 'post',
        "suren_ping_content" => '',
        "suren_ping_date" => '',
    ];
    // 评论的先共用吧，有要求再加类型区分
    for ($i = 1; $i <= $plan_row["post_collect_number"]; $i++) {
        $temp["handle_type"] = $XiaohongshuMajiaPlanResultModel::HANDLE_POST_TYPE_PING;
        $rand_ping_content_key = array_rand($ping_content_list);
        $ping_content = $ping_content_list[$rand_ping_content_key]['content'];
        $temp["handle_content"] = $ping_content;
        $insert_data[] = $temp;
    }
}

//改成待执行
$XiaohongshuMajiaPlanModel->update($plan_id, [
    "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_WAIT,
]);
if (!empty($insert_data)) {
    $XiaohongshuMajiaPlanResultModel->insertBatch($insert_data);
}
$XiaohongshuMajiaPlanModel->update($plan_id, [
    "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
]);