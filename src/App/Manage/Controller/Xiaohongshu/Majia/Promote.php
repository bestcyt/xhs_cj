<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Majia;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Logic\XiaohongshuLogic;
use Mt\Model\LaiguAccountModel;
use Mt\Model\LaiguCustomerXhsModel;
use Mt\Model\PlanMerchantsModel;
use Mt\Model\XiaohongshuMajiaPlanModel;
use Mt\Queue\HandleQueue;
use Mt\Queue\PlanResultQueue;
use Mt\Service\Manage\ManageLoginService;

/**
 * 提交计划
 * Class Promote
 * @package Mt\App\Manage\Controller\Xiaohongshu\Majia
 */
class Promote extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $note_url = trim(strval($this->request->post("note_url")));
            $account_id_arr = $this->request->post("account_id") ?: [];
            $suren_dian_number = intval($this->request->post("suren_dian_number"));
            $shangjia_suren_dian_number = intval($this->request->post("shangjia_suren_dian_number"));
            $shangjia_suren_ping_number = intval($this->request->post("shangjia_suren_ping_number"));

            // 养贴相关
            $post_dian_number = intval($this->request->post("post_dian_number"));
            $post_collect_number = intval($this->request->post("post_collect_number"));
            $post_ping_number = intval($this->request->post("post_ping_number"));

            if (empty($note_url)) {
                $this->error("分享链接不能为空");
            }
            $XiaohongshuLogic = XiaohongshuLogic::getInstance();
            $note_url = $XiaohongshuLogic->getNoteUrlByShareLink($note_url);

            $note_exists = $XiaohongshuLogic->noteExists($note_url);
            if (empty($note_exists)) {
                $this->error("该笔记链接不存在");
            }
            $note_id = $XiaohongshuLogic->getNoteIdByShareUrl($note_url);
            if (empty($note_id)) {
                $this->error("分享链接错误，无法获取笔记加密id" . $note_url);
            }

            $admin_id = ManageLoginService::getInstance()->getCurrentAccountId();
            $LaiguAccountModel = LaiguAccountModel::getInstance();
            $account_id_arr = array_unique(array_filter($account_id_arr));
            $account_arr = $LaiguAccountModel->getMulti($account_id_arr);
            $account_arr = preKey($account_arr, "id");
            //判断商家id的关联素人有没有
            $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
            $shangjia_suren_list_one = $LaiguCustomerXhsModel->getAll([
                'account_id IN' => $account_id_arr,
                'is_delete' => $LaiguCustomerXhsModel::DELETE_NO
            ]);
            $shangjia_suren_id_arr = array_column($shangjia_suren_list_one, "id");
            $shangjia_suren_red_id_arr = array_column($shangjia_suren_list_one, "xhs");
            $shangjia_suren_secret_id_arr = array_column($shangjia_suren_list_one, "secret_id");
            $shangjia_suren_list_one = preKey($shangjia_suren_list_one, "account_id", "id");
            foreach ($account_id_arr as $account_id) {
                if (empty($shangjia_suren_list_one[$account_id])) {
                    $this->error("商家{" . $account_arr[$account_id]['auth_name'] . "}缺少素人");
                }
                foreach ($shangjia_suren_list_one[$account_id] as $value) {
                    if (empty($value["secret_id"])) {
                        $this->error("商家{" . $account_arr[$account_id]['auth_name'] . "}的素人 " . $value["xhs"] . " 还未初始化");
                    }
                }
            }
            $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
            if ($suren_dian_number < 0 || $suren_dian_number > $XiaohongshuMajiaPlanModel::SUREN_DIAN_UP_LIMIT) {
                $this->error("点赞量错误哈");
            }
            if ($shangjia_suren_dian_number < 0 || $shangjia_suren_dian_number > $XiaohongshuMajiaPlanModel::SHANGJIA_DIAN_UP_LIMIT) {
                $this->error("点赞量错误哈");
            }
            if ($shangjia_suren_ping_number < 0 || $shangjia_suren_ping_number > $XiaohongshuMajiaPlanModel::PING_UP_LIMIT) {
                $this->error("评论量错误哈");
            }
            if ($post_dian_number < 0 || $post_dian_number > $XiaohongshuMajiaPlanModel::POST_DIAN_UP_LIMIT) {
                $this->error("点赞量错误哈");
            }
            if ($post_collect_number < 0 || $post_collect_number > $XiaohongshuMajiaPlanModel::POST_COLLECT_UP_LIMIT) {
                $this->error("收藏量错误哈");
            }
            if ($post_ping_number < 0 || $post_ping_number > $XiaohongshuMajiaPlanModel::POST_PING_UP_LIMIT) {
                $this->error("评论量错误哈");
            }

            $majia_plan_insert = [
                'admin_id' => $admin_id,
                'account_id' => implode(',', $account_id_arr),
                'note_url' => $note_url,
                'note_id' => $note_id,
                'suren_dian_number' => $suren_dian_number,
                'suren_dian_up_limit' => $XiaohongshuMajiaPlanModel::SUREN_DIAN_UP_LIMIT,
                'shangjia_suren_account_id' => implode(',', $shangjia_suren_id_arr),
                'shangjia_suren_account' => implode(',', $shangjia_suren_red_id_arr),
                'shangjia_suren_dian_number' => $shangjia_suren_dian_number,
                'shangjia_suren_dian_up_limit' => $XiaohongshuMajiaPlanModel::SHANGJIA_DIAN_UP_LIMIT,
                'shangjia_suren_ping_number' => $shangjia_suren_ping_number,
                'shangjia_suren_ping_up_limit' => $XiaohongshuMajiaPlanModel::PING_UP_LIMIT,

                // 养贴
                'post_dian_number' => $post_dian_number,
                'post_dian_up_limit' => $XiaohongshuMajiaPlanModel::POST_DIAN_UP_LIMIT,
                'post_collect_number' => $post_collect_number,
                'post_collect_up_limit' => $XiaohongshuMajiaPlanModel::POST_COLLECT_UP_LIMIT,
                'post_ping_number' => $post_ping_number,
                'post_ping_up_limit' => $XiaohongshuMajiaPlanModel::PING_UP_LIMIT,

                'status' => $XiaohongshuMajiaPlanModel::STATUS_WAIT,
                'create_time' => time(),
            ];
            $id = $XiaohongshuMajiaPlanModel->insert($majia_plan_insert);
            if (empty($id)) {
                $this->error("sad 添加失败");
            }
            $plan_merchant_insert = [];
            $PlanMerchantsModel = PlanMerchantsModel::getInstance();
            foreach ($account_id_arr as $account_id) {
                $plan_merchant_insert[] = [
                    'plan_type' => $PlanMerchantsModel::TYPE_MAJIA,
                    'account_id' => $account_id,
                    'plan_id' => $id,
                    'create_time' => time(),
                ];
            }
            $PlanMerchantsModel->insertBatch($plan_merchant_insert);
            // 有评论素人的情况
            if (($suren_dian_number + $shangjia_suren_dian_number + $shangjia_suren_ping_number) > 0) {
                //分发操作
                $HandleQueue = HandleQueue::getInstance();
                $HandleQueue->produce([
                    "handle_type" => "majia_handle",
                    "note_url" => $note_url,
                    "data" => [
                        [
                            "handle_type" => 999,
                            "surenSecretId" => $shangjia_suren_secret_id_arr,
                            "id" => $id,
                            "comment_id" => "",
                            "ping_content" => "",
                            "dian_max" => "",
                        ]
                    ],
                ]);
            }else {
                // 只有养贴数据的情况
                PlanResultQueue::getInstance()->produce($id);
            }
            

            $this->success("提交成功，稍后在计划管理里查看结果");
        }
    }

    protected function view()
    {
        $account_arr = LaiguAccountModel::getInstance()->getAll(['is_delete' => LaiguAccountModel::DELETE_NO]);
        View::getInstance()
            ->assign("account_arr", $account_arr)
            ->render();
    }
}