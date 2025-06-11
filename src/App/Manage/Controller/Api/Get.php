<?php

namespace Mt\App\Manage\Controller\Api;

use Mt\App\Manage\Controller\BaseController;
use Mt\Cache\XhsWebSessionCache;
use Mt\Lock\MajiaPlanDispatchLock;
use Mt\Model\XiaohongshuAccountModel;
use Mt\Model\XiaohongshuMajiaPlanModel;
use Mt\Model\XiaohongshuMajiaPlanResultModel;
use Mt\Queue\HandleQueue;

/**
 * 获取事件
 * Class Get
 * @package Mt\App\Manage\Controller\Api
 */
class Get extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $secret_id = trim(strval($this->request->post("secret_id")));
            $web_session = trim(strval($this->request->post("web_session")));
            if (!empty($secret_id) || !empty($web_session)) {
                //存储小红书登录 web_session 用于判断小红书笔记是否存在
                $XhsWebSessionCache = XhsWebSessionCache::getInstance();

                $XhsWebSessionCache->set("web_session", $web_session);
            }

            $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
            $account = $XiaohongshuAccountModel->getOneBySecretId($secret_id);
            //风控就不再进行任务分发
            if (!empty($account) && !empty($account["risk_info"])) {
//                $this->success([]);
            }
            $HandleQueue = HandleQueue::getInstance();
            $data = $HandleQueue->consume();
            if (empty($data)) {
                if (empty($account) || empty($account["red_id"])) {
                    $data = [];
                } elseif ($account["is_dian"] == $XiaohongshuAccountModel::DIAN_NO
                    && $account["is_ping"] == $XiaohongshuAccountModel::PING_NO
                    && $account["is_collect"] == $XiaohongshuAccountModel::COLLECT_NO) {
                    $data = [];
                } else {
                    //获取马甲计划
                    $XiaohongshuMajiaPlanResultModel = XiaohongshuMajiaPlanResultModel::getInstance();
                    $temp_begin_time = time() - 86400 * 10;
                    $MajiaPlanDispatchLock = MajiaPlanDispatchLock::getInstance();
                    $intUniqueLockId = $MajiaPlanDispatchLock->addLock("dispatch");
                    if (!$intUniqueLockId) {
                        $plan_result = [];
                    } else {
                        $plan_result = $XiaohongshuMajiaPlanResultModel->getRow([
                            "create_time >=" => $temp_begin_time,
                            "handle_account_id" => 0,
                            "plan_id not in(select plan_id from xiaohongshu_majia_plan_result where handle_account_id={$account["id"]} and status<=1 and create_time>=" . $temp_begin_time . " and dispatch_time>=" . (time() - 3600 * 8) . ")",//排除自己分配过且进行中或成功 的计划    避免个人重复处理一个帖子
                            "plan_id not in(select plan_id from xiaohongshu_majia_plan_result where create_time>={$temp_begin_time} and dispatch_time>=" . (time() - 180) . " and status<=1)",//排除三分钟内被分配过且进行中或成功 的计划    避免同个帖子短时间内扎堆马甲操作
                        ], "id");
                    }
                    if (!empty($plan_result)) {
                        $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
                        $plan_row = $XiaohongshuMajiaPlanModel->getOne($plan_result["plan_id"]);
                        $plan_result = $XiaohongshuMajiaPlanResultModel->getAll([
                            "plan_id" => $plan_result["plan_id"],
                            "handle_account_id" => 0,
                        ]);
                        shuffle($plan_result);//打散 避免多个分发时同序冲突
                        $handle_data = [];
                        $ping_flag = false;
                        $dian_comment = [];

                        // 养贴相关
                        $post_dian_flag = false;
                        $post_ping_flag = false;
                        $post_collect_flag = false;

                        $update = [];
                        // 单账号完成单贴的多个任务
                        foreach ($plan_result as $key => $value) {
                            $dian_max = $value["suren_type"] == 2 ? $plan_row["shangjia_suren_dian_up_limit"] : $plan_row["suren_dian_up_limit"];
                            if ($value["handle_type"] == 1 && $account["is_dian"] == $XiaohongshuAccountModel::DIAN_YES) {
                                //一条评论只能点一个赞
                                if (!in_array($value["suren_comment_id"], $dian_comment)) {
                                    $dian_comment[] = $value["suren_comment_id"];
                                    $handle_data[$value["id"]] = [
                                        "handle_type" => $value["handle_type"],
                                        "comment_id" => $value["suren_comment_id"],
                                        "id" => $value["id"],
                                        "ping_content" => "",
                                        "dian_max" => $dian_max,
                                    ];
                                    $update[] = $value["id"];
                                }
                            } elseif ($value["handle_type"] == 2 && $account["is_ping"] == $XiaohongshuAccountModel::PING_YES) {
                                //一个帖子只能一个评价
                                if (empty($ping_flag)) {
                                    $handle_data[$value["id"]] = [
                                        "handle_type" => $value["handle_type"],
                                        "comment_id" => $value["suren_comment_id"],
                                        "id" => $value["id"],
                                        "ping_content" => $value["handle_content"],//评论内容
                                        "dian_max" => $dian_max,
                                    ];
                                    $update[] = $value["id"];
                                    $ping_flag = true;
                                }
                            } elseif ($value["handle_type"] == 11 && $account["is_dian"] == $XiaohongshuAccountModel::DIAN_YES) {
                                //一个帖子只能一个赞
                                if (empty($post_dian_flag)) {
                                    $handle_data[$value["id"]] = [
                                        "handle_type" => $value["handle_type"],
                                        "comment_id" => $value["suren_comment_id"],
                                        "id" => $value["id"],
                                        "ping_content" => "",
                                        "dian_max" => $plan_row["post_dian_up_limit"],
                                    ];
                                    $update[] = $value["id"];
                                    $post_dian_flag = true;
                                }
                            } elseif ($value["handle_type"] == 12 && $account["is_ping"] == $XiaohongshuAccountModel::PING_YES) {
                                //一个帖子只能一个评价
                                if (empty($post_ping_flag)) {
                                    $handle_data[$value["id"]] = [
                                        "handle_type" => $value["handle_type"],
                                        "comment_id" => $value["suren_comment_id"],
                                        "id" => $value["id"],
                                        "ping_content" => $value["handle_content"],//评论内容
                                        "dian_max" => $plan_row["post_ping_up_limit"],
                                    ];
                                    $update[] = $value["id"];
                                    $post_ping_flag = true;
                                }
                            } elseif ($value["handle_type"] == 13 && $account["is_collect"] == $XiaohongshuAccountModel::COLLECT_YES) {
                                //一个帖子只能一个收藏
                                if (empty($post_collect_flag)) {
                                    $handle_data[$value["id"]] = [
                                        "handle_type" => $value["handle_type"],
                                        "comment_id" => $value["suren_comment_id"],
                                        "id" => $value["id"],
                                        "ping_content" => '',
                                        "dian_max" => $plan_row["post_collect_up_limit"],
                                    ];
                                    $update[] = $value["id"];
                                    $post_collect_flag = true;
                                }
                            }
                        }
                        if (!empty($update)) {
                            foreach ($update as $tempId) {
                                $result = $XiaohongshuMajiaPlanResultModel->updateWhere([
                                    "id" => $tempId,
                                    "handle_account_id" => 0,
                                ], [
                                    "dispatch_time" => time(),
                                    "handle_account_id" => $account["id"],
                                ]);
                                if (empty($result) || $XiaohongshuMajiaPlanResultModel->affectedRows() <= 0) {
                                    unset($handle_data[$tempId]);
                                }
                            }
                        }
                        if (!empty($handle_data)) {
                            $handle_data = array_values($handle_data);
                            $handle_data = array_sort($handle_data, "handle_type", SORT_DESC);
                            $data = [
                                "handle_type" => "majia_handle",
                                "plan_id" => $plan_row["id"],
                                "note_url" => $plan_row["note_url"],
                                "data" => $handle_data,
                            ];
                        }
                        //解锁
                        $MajiaPlanDispatchLock->releaseLock("dispatch", $intUniqueLockId);
                    }
                }
            } else {
                if ($data["handle_type"] == "majia_handle") {
                    //预查询记录下分配人
                    if ($data["data"][0]["handle_type"] == 999) {
                        $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
                        $XiaohongshuMajiaPlanModel->update($data["data"][0]["id"], [
                            "query_red_id" => $account ? $account["red_id"] : $secret_id,
                            "query_begin_time" => time(),
                        ]);
                    }
                }
            }
            $this->success($data);
        }
    }
}