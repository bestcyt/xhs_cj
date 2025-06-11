<?php

namespace Mt\App\Manage\Controller\Xiaohongshu\Remark;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\XiaohongshuAccountModel;
use Mt\Model\XiaohongshuRemarkModel;

/**
 * 删除
 * Class Delete
 * @package Mt\App\Manage\Controller\Xiaohongshu\Account
 */
class Delete extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $XiaohongshuRemarkModel = XiaohongshuRemarkModel::getInstance();
            $remark_id = intval($this->request->post("id"));

            $update = [];
            //当前账号删除对应的关联
            $res = $XiaohongshuRemarkModel->update($remark_id, ['is_delete' => $XiaohongshuRemarkModel::DELETE_YES]);

            if (empty($res)) {
                $this->error();
            }
            $this->success();
        }
    }
}