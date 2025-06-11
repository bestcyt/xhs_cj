<?php

namespace Mt\App\Manage\Controller\Api;

use Mt\App\Manage\Controller\BaseController;

/**
 * 下载插件
 * Class PluginDownload
 * @package Mt\App\Manage\Controller\Api
 */
class PluginDownload extends BaseController
{
    public function main()
    {
        $filename = 'chrome_plugin';
        $zip_file = $filename . '.zip';
        $temp_dir = app_env('app.log_path') . "/" . $zip_file;

        $plugin_file = "chrome_plugin";

        $command = "cd " . app_root_path() . " && zip -r " . $temp_dir . ' ' . $plugin_file;
        shell_exec($command);

        // 返回zip文件
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $zip_file);
        header('Content-Length: ' . filesize($temp_dir));
        readfile($temp_dir);

        // 清理临时文件
        unlink($temp_dir);
        exit;
    }
}