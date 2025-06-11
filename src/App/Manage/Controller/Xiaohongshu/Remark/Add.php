<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Remark;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Model\XiaohongshuRemarkModel;

/**
 * 添加
 * Class Add
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Add extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $XiaohongshuRemarkModel = XiaohongshuRemarkModel::getInstance();
            $content = trim(strval($this->request->post("content")));
            $admin_id = intval(($this->request->post("admin_id")));

            $res = $XiaohongshuRemarkModel->insert([
                'create_time' => time(),
                'admin_id' => $admin_id ,
                'content' => $content,
                'is_delete' => $XiaohongshuRemarkModel::DELETE_NO,
            ]);

            if (!$res) {
                $this->error('添加失败');
            }
            $this->success();
        }
    }

    protected function view()
    {
        $ManageAccountModel = ManageAccountModel::getInstance();
        $admin_arr = $ManageAccountModel->getAll([]);
        View::getInstance()
            ->assign("admin_arr", $admin_arr)
            ->render();
    }
}