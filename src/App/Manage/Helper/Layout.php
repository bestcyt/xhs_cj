<?php

namespace Mt\App\Manage\Helper;

use Mt\Lib\Traits\HelperTrait;
use Mt\Model\Manage\ManageAccountModel;
use Mt\Model\Manage\ManageRightsModel;
use Mt\Service\Manage\ManageLoginService;

/**
 * 布局
 */
class Layout
{
    use HelperTrait;

    //头部(layui)
    public static function headerLayUi($noView = false)
    {
        $data = [
            'cur_acct_info' => ManageLoginService::getInstance()->getCurrentAccount(),
            'menus' => self::_menus(),
        ];
        if ($noView) {
            return $data;
        }
        self::renderStatic("headerLayUi", $data);
    }

    //尾部(layui)

    public static function _menus()
    {
        $menus = app_config("menu");
        return self::formatMenu($menus);
    }


    //菜单

    public static function rightsFlagArr()
    {
        static $flagArr = null;
        if ($flagArr !== null) {
            return $flagArr;
        }
        $ManageAccountModel = ManageAccountModel::getInstance();
        $flagArr = $ManageAccountModel->getAllowRightsFlagArr(ManageLoginService::getInstance()->getCurrentAccount(), ManageRightsModel::SYSTEM_MANAGE);
        return $flagArr;
    }

    //用户的所有权限标识

    public static function footerLayUi()
    {
        self::renderStatic("footerLayUi");
    }

    protected static function formatMenu(array $menus)
    {
        //无权限的隐藏
        $rightsFlagArr = self::rightsFlagArr();

        $uniqueKey = uniqid() . "_" . rand_string(10);
        foreach ($menus as $key => $value) {
            if (!isset($value["flag"]) || (!in_array($value["flag"], $rightsFlagArr) && !in_array("_all", $rightsFlagArr))) {
                unset($menus[$key]);
                continue;
            }
            $menus[$key]["id"] = $uniqueKey . "_" . $key;
            $menus[$key]["url"] = U($value["url"]);
            if (!empty($value["controller_menus"])) {
                foreach ($value["controller_menus"] as $k => $val) {
                    if (!isset($val["flag"]) || (!in_array($val["flag"], $rightsFlagArr) && !in_array("_all", $rightsFlagArr))) {
                        unset($menus[$key]["controller_menus"][$k]);
                        continue;
                    }
                    $menus[$key]['controller_menus'][$k]["url"] = U($val["url"]);
                    $menus[$key]['controller_menus'][$k]["id"] = $uniqueKey . "_" . $key . "_" . $k;
                    if (!empty($val["action_menus"])) {
                        foreach ($val["action_menus"] as $t => $v) {
                            if (!isset($v["flag"]) || (!in_array($v["flag"], $rightsFlagArr) && !in_array("_all", $rightsFlagArr))) {
                                unset($menus[$key]["controller_menus"][$k]["action_menus"][$t]);
                                continue;
                            }
                            $menus[$key]['controller_menus'][$k]['action_menus'][$t]['url'] = U($v["url"]);
                            $menus[$key]['controller_menus'][$k]['action_menus'][$t]['id'] = $uniqueKey . "_" . $key . "_" . $k . "_" . $t;
                        }
                        $menus[$key]['controller_menus'][$k]["action_menus"] = array_values($menus[$key]['controller_menus'][$k]["action_menus"]);
                        if (empty($menus[$key]['controller_menus'][$k]["action_menus"])) {
                            unset($menus[$key]['controller_menus'][$k]);
                        }
                    }
                }
                $menus[$key]["controller_menus"] = array_values($menus[$key]["controller_menus"]);
                if (empty($menus[$key]['controller_menus'])) {
                    unset($menus[$key]);
                }
            }
        }
        return array_values($menus);
    }

}