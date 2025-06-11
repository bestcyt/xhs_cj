<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Majia;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Logic\XiaohongshuLogic;
use Mt\Model\LaiguAccountModel;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Model\XiaohongshuMajiaPlanModel;


/**
 * 数据查询
 * Class Record
 * @package Mt\App\Manage\Controller\Xiaohongshu\Majia
 */
class Record extends BaseController
{
    public function main()
    {
        $status = intval($this->request->get("status"));
        $admin_id = intval($this->request->get("admin_id"));
        $note_url = trim(strval($this->request->get("note_url")));
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 20;
        $begin_ymd = trim($this->request->get("time_begin"));
        $end_ymd = trim($this->request->get("time_end"));
        $plan_id = intval($this->request->get("plan_id"));
        $auth_name = trim($this->request->get("auth_name"));

        $where = [];
        if (!empty($auth_name)) {
            // 模糊查询account id list 
            $account_list = LaiguAccountModel::getInstance()->getAll(['auth_name LIKE' => "%{$auth_name}%"]);
            if (!empty($account_list)) {
                $where["account_id IN"] = array_column($account_list, 'id');
            }
        }
        if ($begin_ymd) {
            $begin_time = strtotime($begin_ymd);
            $where['create_time >='] = $begin_time;
        }
        if ($end_ymd) {
            $end_time = strtotime($end_ymd) + 86400;
            $where['create_time <='] = $end_time;
        }

        if (!empty($plan_id)) {
            $where["id"] = $plan_id;
        }
        if (!empty($admin_id)) {
            $where["admin_id"] = $admin_id;
        }
        if (!empty($status)) {
            $where["status"] = $status;
        }
        if (!empty($note_url)) {
            $XiaohongshuLogic = XiaohongshuLogic::getInstance();
            $note_id = $XiaohongshuLogic->getNoteIdByShareUrl($note_url);
            $where["note_id"] = $note_id;
        }
        //商家查询
        $account_id = intval($this->request->get("account_id"));
        if (!empty($account_id)) {
            $where[] = "id in(select plan_id from plan_merchants where account_id={$account_id} and plan_type=1)";
        }

        $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
        $data = $XiaohongshuMajiaPlanModel->getPageList($where, $page, $count, "id desc");
        if (!empty($data)) {
            $status_arr = $XiaohongshuMajiaPlanModel::STATUS;
            $l_account_list = ManageAccountModel::getInstance()->getAll([]);
            $l_account_list = preKey($l_account_list, 'id');

            $account_list = LaiguAccountModel::getInstance()->getAll([]);
            $account_list = preKey($account_list, 'id');
            foreach ($data as $key => $value) {
                $data[$key]["status_name"] = $status_arr[$value["status"]];
                $data[$key]["create_time"] = $value["create_time"] ? date("Y-m-d H:i:s", $value["create_time"]) : "";
                $data[$key]["running_time"] = $value["running_time"] ? date("Y-m-d H:i:s", $value["running_time"]) : "";
                $data[$key]["query_begin_time"] = $value["query_begin_time"] ? date("Y-m-d H:i:s", $value["query_begin_time"]) : "";
                $data[$key]['admin_name'] = $l_account_list[$value['admin_id']]['real_name'] ?? '';
//                关联商家
                $data[$key]['account_name'] = '';
                $account_arr = explode(',', $value['account_id']);
                $account_arr = array_filter($account_arr);
                foreach ($account_arr as $item) {
                    if (empty($item)) {
                        continue;
                    }
                    $data[$key]['account_name'] .= '【' . $account_list[$item]['auth_name'] . '】';
                }
            }
        }
        $total = $XiaohongshuMajiaPlanModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    protected function view()
    {
        $XiaohongshuMajiaPlanModel = XiaohongshuMajiaPlanModel::getInstance();
        $ManageAccountModel = ManageAccountModel::getInstance();
        $status_arr = $XiaohongshuMajiaPlanModel::STATUS;
        $LaiguAccountModel = LaiguAccountModel::getInstance();
        $laigu_account_arr = $LaiguAccountModel->getAll([
            "is_delete" => $LaiguAccountModel::DELETE_NO,
        ]);
        $admin_arr = $ManageAccountModel->getAll([]);

        View::getInstance()
            ->assign("status_arr", $status_arr)
            ->assign("laigu_account_arr", $laigu_account_arr)
            ->assign("admin_arr", $admin_arr)
            ->render();
    }
}