<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Account;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Lib\Excel;
use Mt\Lib\ExcelYield;
use Mt\Lib\Task;
use Mt\Model\XiaohongshuAccountModel;

/**
 * 账号列表
 * Class Index
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Index extends BaseController
{
    public function main()
    {
        $red_id = trim(strval($this->request->get("red_id")));
        $nickname = trim(strval($this->request->get("nickname")));
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 100;
        $where = [];
        if (!empty($red_id)) {
            $where["red_id LIKE"] = "%{$red_id}%";
        }
        if (!empty($nickname)) {
            $where["nickname LIKE"] = "%{$nickname}%";
        }
        $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();

        // 导出功能
        $export_data = intval($this->request->get("export_data"));
        if (!empty($export_data)) {
            // 使用异步任务导出
            $export_url = Task::getInstance()->asyncWait(self::class, "exportHandle", $where);
            $this->response->redirect($export_url);
            exit;
        }

        $data = $this->data($where, $page, $count);
        $total = $XiaohongshuAccountModel->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    /**
     * 导出处理方法 - 异步执行
     * @param array $where 查询条件
     * @return string 导出文件的URL
     */
    public function exportHandle($where)
    {
        $header = [
            'id' => 'ID',
            'red_id' => '小红书号',
            'nickname' => '小红书昵称',
            'secret_id' => '加密id',
            'dian_name' => '点赞',
            'ping_name' => '评论',
            'collect_name' => '收藏',
        ];

        $data = ExcelYield::getInstance(self::class, "exportData", $where);
        $Excel = Excel::getInstance();
        return $Excel->exportUpload("账号" . ".xlsx", $data, $header);
    }

    /**
     * 导出数据生成器方法
     * @param array $where 查询条件
     * @return \Generator
     */
    public function exportData($where)
    {
        $index_id = 0;
        $page = 1;
        $count = 400;

        while (true) {
            if (!empty($index_id)) {
                $where["id <"] = $index_id;
            }

            $rows = $this->data($where, $page, $count);
            if (empty($rows)) {
                break;
            }

            $index_id = end($rows)["id"];
            foreach ($rows as $temp) {
                yield $temp;
            }
        }
    }

    /**
     *
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $count 每页数量
     * @return array 处理后的数据
     */
    protected function data($where, $page, $count)
    {
        $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
        $data = $XiaohongshuAccountModel->getPageList($where, $page, $count, "id desc");
        if (!empty($data)) {
            $dian_arr = $XiaohongshuAccountModel::DIAN;
            $ping_arr = $XiaohongshuAccountModel::PING;
            $collect_arr = $XiaohongshuAccountModel::COLLECT;
            foreach ($data as $key => $value) {
                $data[$key]["dian_name"] = $dian_arr[$value["is_dian"]];
                $data[$key]["ping_name"] = $ping_arr[$value["is_ping"]];
                $data[$key]["collect_name"] = $collect_arr[$value["is_collect"]];
                $data[$key]["heartbeat_time"] = $value["heartbeat_time"] ? date("Y-m-d H:i:s", $value["heartbeat_time"]) : "--";
                //超过20分钟未上报心跳，标注红色
                if (!empty($value["heartbeat_time"]) && time() - $value["heartbeat_time"] >= 20 * 60) {
                    $data[$key]["logout"] = true;
                } else {
                    $data[$key]["logout"] = false;
                }
            }
        }

        return $data;
    }

    protected function view()
    {
        View::getInstance()
            ->render();
    }
}