<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Majia;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Logic\XiaohongshuLogic;
use Mt\Model\XiaohongshuAccountModel;
use Mt\Model\XiaohongshuMajiaPlanModel;
use Mt\Model\XiaohongshuMajiaPlanResultModel;


/**
 * 马甲执行结果
 * Class Result
 * @package Mt\App\Manage\Controller\Xiaohongshu\Majia
 */
class Result extends BaseController
{
    public function main()
    {
        $status = $this->request->get("status");
        $note_url = trim(strval($this->request->get("note_url")));
        $plan_id = trim(strval($this->request->get("plan_id")));
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 100;
        $where = [];
        if ($status != 999) {
            $where["status"] = $status;
        }
        if (!empty($plan_id)) {
            $where["plan_id"] = $plan_id;
        }
        if (!empty($note_url)) {
            $XiaohongshuLogic = XiaohongshuLogic::getInstance();
            $note_id = $XiaohongshuLogic->getNoteIdByShareUrl($note_url);
            $where[] = "plan_id in(select id from xiaohongshu_majia_plan where note_id='{$note_id}')";
        }

        $XiaohongshuMajiaPlanResultModel = XiaohongshuMajiaPlanResultModel::getInstance();
        $data = $XiaohongshuMajiaPlanResultModel->getPageList($where, $page, $count, "id");
        if (!empty($data)) {
            $plan_info = XiaohongshuMajiaPlanModel::getInstance()->getOne($plan_id);
            $status_arr = $XiaohongshuMajiaPlanResultModel::STATUS;
            $type_arr = $XiaohongshuMajiaPlanResultModel::HANDLE_TYPE;
            $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
            $account_arr = $XiaohongshuAccountModel->getAll([]);
            $account_arr = preKey($account_arr, "id");

            $relation = [];
            foreach ($data as $key => $value) {
                $data[$key]["status_name"] = $status_arr[$value["status"]];
                $data[$key]["handle_time"] = $value["handle_time"] ? date("Y-m-d H:i:s", $value["handle_time"]) : "";
                $data[$key]["dispatch_time"] = $value["dispatch_time"] ? date("Y-m-d H:i:s", $value["dispatch_time"]) : "";
                $data[$key]["type_name"] = $type_arr[$value["handle_type"]];
                $account_info = $account_arr[$value["handle_account_id"]] ?? [];
                $data[$key]["handle_account"] = $account_info ? $account_info["nickname"] . '【' . $account_info["red_id"] . '】' : "";
                //笔记内容
                $data[$key]["note_url"] = $plan_info["note_url"];
                $data[$key]["note_id"] = $plan_info["note_id"];
                $data[$key]["suren_ping_content"] = $value['suren_ping_content'] . '【' . $value['suren_ping_date'] . '】';
                if ($value["parent_id"] > 0) {
                    if (isset($relation[$value["parent_id"]])) {
                        $relation[$value["id"]] = $relation[$value["parent_id"]];
                    } else {
                        $relation[$value["id"]] = $value["parent_id"];
                    }
                    $data[$key]["flag"] = "flag_" . $relation[$value["id"]] . "_" . $value["id"];
                    $data[$key]["id"] = "&nbsp;&nbsp;&nbsp;>>&nbsp;" . $value["id"];
                } else {
                    $data[$key]["flag"] = "flag_" . $value["id"];
                }
            }

            $data = array_sort($data, "flag", SORT_ASC);
        }
        $total = $XiaohongshuMajiaPlanResultModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    protected function view()
    {
        $XiaohongshuMajiaPlanResultModel = XiaohongshuMajiaPlanResultModel::getInstance();
        $status_arr = $XiaohongshuMajiaPlanResultModel::STATUS;
        $plan_id = intval($this->request->get("plan_id"));
        View::getInstance()
            ->assign("status_arr", $status_arr)
            ->assign("plan_id", $plan_id)
            ->render();
    }
}