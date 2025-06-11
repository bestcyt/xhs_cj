<?php

namespace Mt\App\Manage\Controller\Api;

use Mt\App\Manage\Controller\BaseController;
use Mt\Model\XiaohongshuAccountModel;

/**
 * 心跳上报
 * Class Heartbeat
 * @package Mt\App\Manage\Controller\Api
 */
class Heartbeat extends BaseController
{
    public function main()
    {
        if ($this->request->isPost()) {
            $secret_id = trim(strval($this->request->post("secret_id")));
            $XiaohongshuAccountModel = XiaohongshuAccountModel::getInstance();
            $check = $XiaohongshuAccountModel->getOneBySecretId($secret_id);
            $data = [];
            if (empty($check)) {
                $data["need_auth"] = true;
            } else {
                $XiaohongshuAccountModel->update($check["id"], [
                    "heartbeat_time" => time(),
                ]);
            }
            $this->success($data);
        }
    }
}