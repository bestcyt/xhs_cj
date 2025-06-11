<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Remark;

use Fw\View;
use Mt\App\Manage\Controller\BaseController;
use Mt\Model\XiaohongshuRemarkModel;

/**
 * 编辑
 * Class Edit
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Edit extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {

            $remark_id = intval($this->request->post("remark_id"));
            $content = trim(strval($this->request->post("content")));

            $XiaohongshuRemarkModel = XiaohongshuRemarkModel::getInstance();
            $row = $XiaohongshuRemarkModel->getOne($remark_id);
            if (empty($remark_id) || empty($row)) {
                $this->error("非法操作");
            }

            $res = $XiaohongshuRemarkModel->update($remark_id, ['content' => $content]);
            if (empty($res)) {
                $this->error();
            }

            $this->success();
        }
    }

    protected function view()
    {
        $XiaohongshuRemarkModel = XiaohongshuRemarkModel::getInstance();
        $remark_id = intval($this->request->get("id"));
        $remark_info = $XiaohongshuRemarkModel->getOne($remark_id);

        View::getInstance()
            ->assign("remark_info", $remark_info)
            ->assign("remark_id", $remark_id)
            ->render();
    }
}