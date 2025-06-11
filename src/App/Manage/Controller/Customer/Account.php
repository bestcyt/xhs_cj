<?php

namespace Mt\App\Manage\Controller\Customer;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\LaiguAccountModel;
use Mt\Model\LaiguCustomerXhsModel;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Lib\Excel;
use Mt\Lib\ExcelYield;
use Mt\Lib\Task;

/**
 * 商家账号列表
 * Class Index
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Account extends BaseController
{
    public function main()
    {
        $auth_name = trim(strval($this->request->get("auth_name")));
        $admin_id = intval($this->request->get("admin_id"));
        $page = intval($this->request->get("page")) ?: 1;
        $count = intval($this->request->get("count")) ?: 20;

        $where = [
            'is_delete' => LaiguAccountModel::DELETE_NO
        ];
        if (!empty($auth_name)) {
            $where['auth_name LIKE'] = '%' . $auth_name . '%';
        }

        if ($admin_id > 0) {
            $where['admin_id'] = $admin_id;
        }

        // 导出功能
        $export_data = intval($this->request->get("export_data"));
        if (!empty($export_data)) {
            $export_url = Task::getInstance()->asyncWait(self::class, "exportHandle", $where);
            $this->response->redirect($export_url);

            exit;
        }

        $data = $this->data($where, $page, $count);
        
        $total = LaiguAccountModel::getInstance()->getTotal($where);
        $this->success([
            "data" => $data,
            "total" => $total,
        ]);
    }

    /**
     * 获取并处理商家账号数据
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $count 每页数量
     * @return array 处理后的数据
     */
    protected function data($where, $page, $count)
    {
        $LaiguAccountModel = LaiguAccountModel::getInstance();
        $data = $LaiguAccountModel->getPageList($where, $page, $count, "id desc");
        if (empty($data)) {
            return $data;
        }
            
        //小红书账号数量
        $LaiguCustomerXhsModel = LaiguCustomerXhsModel::getInstance();
        $account_xhs_list = $LaiguCustomerXhsModel::getInstance()->db()->from($LaiguCustomerXhsModel->getTable())
            ->multiWhere(['account_id in' => array_column($data, 'id'), 'is_delete' => $LaiguCustomerXhsModel::DELETE_NO])
            ->select('account_id,count(1) as xhs_number')
            ->groupBy('account_id')
            ->fetchAll();
        $account_xhs_list = preKey($account_xhs_list, 'account_id');

        //创建人
        $ManageAccountModel = ManageAccountModel::getInstance();
        $admin_arr = $ManageAccountModel->getMulti(array_column($data, "admin_id"));
        $admin_arr = preKey($admin_arr, "id");

        $status_arr = LaiguAccountModel::DELETE;
        
        foreach ($data as $key => $value) {
            $admin_info = $admin_arr[$value["admin_id"]] ?? [];
            $data[$key]["admin_name"] = $admin_info["real_name"] ?? "";
            $data[$key]["tag_name_str"] = '';
            $data[$key]["xhs_number"] = isset($account_xhs_list[$value['id']]) ? $account_xhs_list[$value['id']]['xhs_number'] : 0;
            $data[$key]["status_name"] = $status_arr[$value['is_delete']] ?? '';
        }
        
        return $data;
    }

    public function view()
    {
        $status_arr = LaiguAccountModel::DELETE;
        $ManageAccountModel = ManageAccountModel::getInstance();
        $admin_arr = $ManageAccountModel->getAll([]);
        View::getInstance()
            ->assign("admin_arr", $admin_arr)
            ->assign("status_arr", $status_arr)
            ->render();

    }
    
    /**
     * 导出处理方法 - 异步执行
     * @param array $where 查询条件
     * @return string 导出文件的URL
     */
    public function exportHandle($where)
    {
        $header = [
            'auth_name' => '商家名称', 
            'admin_name' => '归属员工',
            'xhs_accounts' => '小红书账号列表', 
            'xhs_number' => '小红书账号数量', 
            'status_name' => '删除状态'
        ];
        
        $data = ExcelYield::getInstance(self::class, "exportData", $where);
        $Excel = Excel::getInstance();
        return $Excel->exportUpload("商家基本信息_" . date('Y-m-d') . ".xlsx", $data, $header);
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
            
            $rows = $this->processExportData($where, $page, $count);
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
     * 处理导出数据
     * @param array $where 查询条件
     * @param int $page 页码
     * @param int $count 每页数量
     * @return array 处理后的数据
     */
    public function processExportData($where, $page = 1, $count = 400)
    {
        $data = $this->data($where, $page, $count);
        
        if (empty($data)) {
            return [];
        }
        
        // 获取全部xhs关联
        $xhs_all_list = LaiguCustomerXhsModel::getInstance()->getAll([
            'account_id IN' => array_column($data, 'id'),
            'is_delete' => LaiguCustomerXhsModel::DELETE_NO
        ]);
        
        // 处理数据
        foreach ($data as $key => $value) {
            // 小红书账号列表格式化
            $xhs_info_list = [];
            foreach ($xhs_all_list as $xhs_account) {
                if($xhs_account['account_id'] == $value['id']){
                    $xhs_info_list[] = $xhs_account['xhs'];
                }
            }
            
            // 使用逗号分隔不同的小红书账号
            $data[$key]["xhs_accounts"] = implode(",", $xhs_info_list);
            
            // 处理倒计时显示文本
            $days = $data[$key]["countdown_days"];
            if ($days < 0) {
                $data[$key]["countdown_days_str"] = "已过期" . abs($days) . "天";
            } else {
                $data[$key]["countdown_days_str"] = $days . "天";
            }
        }
        return $data;
    }
    
}