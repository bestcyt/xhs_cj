<?php

namespace Mt\App\Manage\Controller\Api;

use Mt\App\Manage\Controller\BaseController;
use Mt\Counter\MajiaPlanNoCommentCounter;
use Mt\Logic\XiaohongshuLogic;
use Mt\Model\LaiguCustomerXhsModel;
use Mt\Model\XiaohongshuAccountModel;
use Mt\Model\XiaohongshuMajiaPlanModel;
use Mt\Model\XiaohongshuMajiaPlanResultModel;
use Mt\Queue\HandleQueue;
use Mt\Queue\PlanResultQueue;

/**
 * 设置事件
 * Class Set
 * @package Mt\App\Manage\Controller\Api
 */
class Set extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $secret_id = trim(strval($this->request->post("secret_id")));
            if (empty($secret_id)) {
                $this->error("非法操作");
            }
            $result = trim(strval($this->request->post("result", null, "trim")));
            if (empty($result)) {
                $this->error("非法操作");
            }
            $result = json_decode($result, true);
            $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
            $current_account = $XiaohongshuAccountModel->getOneBySecretId($secret_id);
            if (!empty($result["handle_type"]) && $result["handle_type"] == "user_info_set" && $result["secret_id"] == $secret_id) {
                //小红书账号添加
                $account = $XiaohongshuAccountModel->getOneBySecretId($result["secret_id"]);
                if (empty($account)) {
                    $XiaohongshuAccountModel->insert([
                        "red_id" => $result["red_id"],
                        "secret_id" => $result["secret_id"],
                        "nickname" => $result["nickname"],
                        "create_time" => time(),
                        "heartbeat_time" => time(),
                        "is_ping" => $XiaohongshuAccountModel::PING_YES,
                        "is_dian" => $XiaohongshuAccountModel::DIAN_YES,
                        "is_collect" => $XiaohongshuAccountModel::COLLECT_YES,
                    ]);
                }
            } elseif (!empty($result["handle_type"]) && $result["handle_type"] == "user_search") {
                //商家小红书账号信息查询
                $red_id = $result["red_id"];
                $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
                if ($result["exists"]) {
                    $LaiguCustomerXhsModel->updateWhere([
                        "xhs" => $red_id,
                    ], [
                        "secret_id" => $result["secret_id"],
                        "nickname" => $result["user_name"],
                        "remark" => "",
                    ]);
                } else {
                    $LaiguCustomerXhsModel->updateWhere([
                        "xhs" => $red_id,
                    ], [
                        "secret_id" => "",
                        "nickname" => "",
                        "remark" => "小红书号不存在",
                    ]);
                }
            } elseif (!empty($result["handle_type"]) && in_array($result["handle_type"], ['verify_risk', 'verify_qrcode_risk'])) {
                $risk_info = $result["handle_type"] == "verify_risk" ? "提示需要验证" : $result["result"];
                $XiaohongshuAccountModel->update($current_account["id"], [
                    "risk_info" => $risk_info . " " . date("Y-m-d H:i:s"),
                ]);
            } elseif (!empty($result["handle_type"]) && $result["handle_type"] == "majia_handle") {
                $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
                $PlanResultQueue = PlanResultQueue::getInstance();
                $XiaohongshuMajiaPlanResultModel = XiaohongshuMajiaPlanResultModel::getInstance();
                $XiaohongshuLogic = XiaohongshuLogic::getInstance();
                $HandleQueue = HandleQueue::getInstance();
                $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
                $MajiaPlanNoCommentCounter = MajiaPlanNoCommentCounter::getInstance();
                $majia_plan_note_exists = null;
                foreach ($result["result"] as $value) {
                    //预查询
                    if ($value["handle_type"] == 999) {
                        if ($value["status"] == 1) {
                            $success_status = array_column($value["result"], "status");
                            //所有素人都没有评论
                            if (!in_array(1, $success_status) && !in_array("1", $success_status)) {
                                $update = [
                                    "status" => $XiaohongshuMajiaPlanModel::STATUS_QUERY_FAIL,
                                    "result" => $value["result"][0]["result"],
                                    "query_result" => json_encode($value["result"], JSON_UNESCAPED_UNICODE),
                                ];
                                $value["status"] = 2;
                                //未找到评论区 重试三次
                                if ($value["result"][0]["result"] == "未找到该素人评论") {
                                    if ($MajiaPlanNoCommentCounter->incr($value["id"]) <= 3) {
                                        $update = [];
                                        $plan_row = $XiaohongshuMajiaPlanModel->getOne($value["id"]);
                                        $shangjiaXhsArr = $LaiguCustomerXhsModel->getMulti(explode(",", $plan_row["shangjia_suren_account_id"]));
                                        $HandleQueue->produce([
                                            "handle_type" => "majia_handle",
                                            "note_url" => $plan_row["note_url"],
                                            "data" => [
                                                [
                                                    "handle_type" => 999,
                                                    "surenSecretId" => array_column($shangjiaXhsArr, "secret_id"),
                                                    "id" => $value["id"],
                                                    "comment_id" => "",
                                                    "ping_content" => "",
                                                    "dian_max" => "",
                                                ]
                                            ],
                                        ]);
                                    }
                                }
                            } else {
                                $update = [
                                    "status" => $XiaohongshuMajiaPlanModel::STATUS_QUERY_DONE,
                                    "result" => "",
                                    "query_result" => json_encode($value["result"], JSON_UNESCAPED_UNICODE),
                                ];
                            }
                        } else {
                            $update = [
                                "status" => $XiaohongshuMajiaPlanModel::STATUS_QUERY_FAIL,
                                "result" => $value["result"],
                                "query_result" => json_encode($value["result"], JSON_UNESCAPED_UNICODE),
                            ];
                            //笔记不存在要进一步判断，误判的情况下要重新进入预查询
                            if (!is_array($value["result"]) && $value["result"] == '笔记不存在') {
                                $plan_row = $XiaohongshuMajiaPlanModel::getInstance()->getOne($value["id"]);
                                if ($XiaohongshuLogic->noteExists($plan_row["note_url"])) {
                                    $update = [];
                                    $shangjiaXhsArr = $LaiguCustomerXhsModel->getMulti(explode(",", $plan_row["shangjia_suren_account_id"]));
                                    $HandleQueue->produce([
                                        "handle_type" => "majia_handle",
                                        "note_url" => $plan_row["note_url"],
                                        "data" => [
                                            [
                                                "handle_type" => 999,
                                                "surenSecretId" => array_column($shangjiaXhsArr, "secret_id"),
                                                "id" => $value["id"],
                                                "comment_id" => "",
                                                "ping_content" => "",
                                                "dian_max" => "",
                                            ]
                                        ],
                                    ]);
                                }
                            }
                        }
                        if (!empty($update)) {
                            $XiaohongshuMajiaPlanModel->update($value["id"], $update);
                        }
                        //生成点赞和评论任务
                        if ($value["status"] == 1) {
                            $PlanResultQueue->produce($value["id"]);
                        }
                    } elseif ($value["handle_type"] == 1 || $value["handle_type"] == 2) {
                        //点赞  status 1成功 2、3 失败  id
                        $XiaohongshuMajiaPlanResultModel->update($value["id"], [
                            "status" => $value["status"],
                            "result" => $value["result"],
                            "handle_time" => time(),
                        ]);
                        //评论禁言，将账号标记成禁言
                        if ($value["status"] == 2 && $value["handle_type"] == 2 && strpos($value["result"], "禁言") !== false) {
                            $XiaohongshuAccountModel->update($current_account["id"], [
                                "risk_info" => $value["result"] . " " . date("Y-m-d H:i:s"),
                                "is_ping" => $XiaohongshuAccountModel::PING_NO,
                            ]);
                            // 被禁言需要换号 增加一条again_id 执行
                            $current_result = $XiaohongshuMajiaPlanResultModel->getOne($value["id"]);
                            unset($current_result["id"]);
                            $current_result["handle_account_id"] = 0;
                            $current_result["handle_account"] = "";
                            $current_result["handle_time"] = 0;
                            $current_result["dispatch_time"] = 0;
                            $current_result["create_time"] = time();
                            $current_result["result"] = "";
                            $current_result["again_id"] = 0;
                            $current_result["parent_id"] = $value["id"];
                            $current_result["status"] = 0;
                            $current_result["ori_id"] = $current_result["ori_id"] ?: $value["id"];

                            $again_id = $XiaohongshuMajiaPlanResultModel->insert($current_result);
                            if (!empty($again_id)) {
                                $XiaohongshuMajiaPlanResultModel->update($value["id"], [
                                    "again_id" => $again_id,
                                ]);
                                $XiaohongshuMajiaPlanModel->update($current_result["plan_id"], [
                                    "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
                                ]);
                            }
                        }
                        //如果笔记不存在，进一步校验是否真实不存在，然后重试
                        if ($value["status"] != 1 && in_array($value["result"], ['评论不存在(删除或没加载出来)', "笔记不存在"])) {
                            if ($value['result'] == "笔记不存在" && $majia_plan_note_exists === null) {
                                $plan_result = $XiaohongshuMajiaPlanResultModel->getOne($value["id"]);
                                $plan_temp = $XiaohongshuMajiaPlanModel->getOne($plan_result["plan_id"]);
                                $majia_plan_note_exists = $XiaohongshuLogic->noteExists($plan_temp["note_url"]);
                            } elseif ($value['result'] == "评论不存在(删除或没加载出来)") {
                                $majia_plan_note_exists = true;
                            }
                            if (!empty($majia_plan_note_exists)) {
                                $current_result = $XiaohongshuMajiaPlanResultModel->getOne($value["id"]);
                                unset($current_result["id"]);
                                $current_result["handle_account_id"] = 0;
                                $current_result["handle_account"] = "";
                                $current_result["handle_time"] = 0;
                                $current_result["dispatch_time"] = 0;
                                $current_result["create_time"] = time();
                                $current_result["result"] = "";
                                $current_result["again_id"] = 0;
                                $current_result["parent_id"] = $value["id"];
                                $current_result["status"] = 0;
                                $current_result["ori_id"] = $current_result["ori_id"] ?: $value["id"];
                                if ($value['result'] == "评论不存在(删除或没加载出来)" && $MajiaPlanNoCommentCounter->incr("result_" . $current_result["ori_id"]) > 3) {
                                    continue;
                                }
                                $again_id = $XiaohongshuMajiaPlanResultModel->insert($current_result);
                                if (!empty($again_id)) {
                                    $XiaohongshuMajiaPlanResultModel->update($value["id"], [
                                        "again_id" => $again_id,
                                    ]);
                                    $XiaohongshuMajiaPlanModel->update($current_result["plan_id"], [
                                        "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
                                    ]);
                                }
                            }
                        }
                    } elseif ($value["handle_type"] == 11 || $value["handle_type"] == 12 || $value["handle_type"] == 13) {
                        //点赞  status 1成功 2、3 失败  id
                        $XiaohongshuMajiaPlanResultModel->update($value["id"], [
                            "status" => $value["status"],
                            "result" => $value["result"],
                            "handle_time" => time(),
                        ]);
                        //评论禁言，将账号标记成禁言
                        if ($value["status"] == 2 && $value["handle_type"] == 12 && strpos($value["result"], "禁言") !== false) {
                            $XiaohongshuAccountModel->update($current_account["id"], [
                                "risk_info" => $value["result"] . " " . date("Y-m-d H:i:s"),
                                "is_ping" => $XiaohongshuAccountModel::PING_NO,
                            ]);

                            // 被禁言需要换号 增加一条again_id 执行
                            $current_result = $XiaohongshuMajiaPlanResultModel->getOne($value["id"]);
                            unset($current_result["id"]);
                            $current_result["handle_account_id"] = 0;
                            $current_result["handle_account"] = "";
                            $current_result["handle_time"] = 0;
                            $current_result["dispatch_time"] = 0;
                            $current_result["create_time"] = time();
                            $current_result["result"] = "";
                            $current_result["again_id"] = 0;
                            $current_result["parent_id"] = $value["id"];
                            $current_result["status"] = 0;
                            $current_result["ori_id"] = $current_result["ori_id"] ?: $value["id"];

                            $again_id = $XiaohongshuMajiaPlanResultModel->insert($current_result);
                            if (!empty($again_id)) {
                                $XiaohongshuMajiaPlanResultModel->update($value["id"], [
                                    "again_id" => $again_id,
                                ]);
                                $XiaohongshuMajiaPlanModel->update($current_result["plan_id"], [
                                    "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
                                ]);
                            }

                        }
                        //如果笔记不存在，进一步校验是否真实不存在，然后重试
                        if ($value["status"] != 1 && in_array($value["result"], ["笔记不存在"])) {
                            if ($value['result'] == "笔记不存在" && $majia_plan_note_exists === null) {
                                $plan_result = $XiaohongshuMajiaPlanResultModel->getOne($value["id"]);
                                $plan_temp = $XiaohongshuMajiaPlanModel->getOne($plan_result["plan_id"]);
                                $majia_plan_note_exists = $XiaohongshuLogic->noteExists($plan_temp["note_url"]);
                            }
                            if (!empty($majia_plan_note_exists)) {
                                $current_result = $XiaohongshuMajiaPlanResultModel->getOne($value["id"]);
                                unset($current_result["id"]);
                                $current_result["handle_account_id"] = 0;
                                $current_result["handle_account"] = "";
                                $current_result["handle_time"] = 0;
                                $current_result["dispatch_time"] = 0;
                                $current_result["create_time"] = time();
                                $current_result["result"] = "";
                                $current_result["again_id"] = 0;
                                $current_result["parent_id"] = $value["id"];
                                $current_result["status"] = 0;
                                $current_result["ori_id"] = $current_result["ori_id"] ?: $value["id"];

                                $again_id = $XiaohongshuMajiaPlanResultModel->insert($current_result);
                                if (!empty($again_id)) {
                                    $XiaohongshuMajiaPlanResultModel->update($value["id"], [
                                        "again_id" => $again_id,
                                    ]);
                                    $XiaohongshuMajiaPlanModel->update($current_result["plan_id"], [
                                        "status" => $XiaohongshuMajiaPlanModel::STATUS_RUN_ING,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            $this->success($result);
        }
    }
}