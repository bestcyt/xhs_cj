<?php

namespace Mt\App\Manage\Controller;


class Error extends BaseController
{
    /**
     * @param \Exception $e
     */
    public function main($e)
    {
        phpErrorCallback($e);
        if ($this->request->isAjax()) {
            $this->error($e->getMessage(), [], 500);
        } else {
            $code = $e->getCode();
            if (!empty($code)) {
                echo "<pre>" . $code . "</pre>";
            }
            echo "<pre>" . $e->getMessage() . "</pre>";
            echo "<pre>" . ($e->getTraceAsString()) . "</pre>";
        }
    }

}