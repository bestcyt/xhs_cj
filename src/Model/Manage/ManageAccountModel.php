<?php
/**
 * 管理员账号管理
 */

namespace Mt\Model\Manage;

use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

class ManageAccountModel extends Model
{
    use InstanceTrait;
    use TableTrait;

    CONST PASSWORD_PREFIX = 'account.ccc.mtr';

    //用户状态
    const STATUS_NORMAL = 1; //正常
    const STATUS_DISABLED = 2; //禁用
    const STATUS = [
        self::STATUS_NORMAL => "正常",
        self::STATUS_DISABLED => "禁用",
    ];

    //是否root用户
    const ROOT_YES = 1;
    const ROOT_NO = 2;
    const ROOT = [
        self::ROOT_YES => "是",
        self::ROOT_NO => "否",
    ];

    protected function __construct()
    {
        $this->table = 'accounts';
        $this->dbGroup = $this->getMainDbGroup();
    }

    public static function encrypt_password($password)
    {
        return md5(self::PASSWORD_PREFIX . $password);
    }

    public static function checkPasswordFormat($password)
    {
        if (!$password) {
            return false;
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 15) {
            return false;
        }
        if (!preg_match("/^[0-9A-Za-z\_\!\@\#\$\%\^\&\*\(\)]+$/", $password)) {
            return false;
        }
        if (preg_match("/^\d+$/", $password)) {
            return false;
        }
        if (preg_match("/^[a-zA-Z]+$/", $password)) {
            return false;
        }
        if (preg_match("/^_+$/", $password)) {
            return false;
        }
        return true;
    }

    public function getOneByEmail($email)
    {
        return $this->getRow([
            "email" => $email,
        ]);
    }

    public function getOneByMobile($mobile)
    {
        return $this->getRow([
            "mobile" => $mobile,
        ]);
    }

    /**
     * 是否有权限
     * @param $accountInfo
     * @param $api_url
     * @param $system_id
     * @return bool
     */
    public function hasRights($accountInfo, $api_url, $system_id = ManageRightsModel::SYSTEM_MANAGE)
    {
        if (is_numeric($accountInfo)) {
            $accountInfo = $this->getOne($accountInfo);
        }
        //root用户
        if ($accountInfo["is_root"] == self::ROOT_YES) {
            return true;
        }
        $ManageAccountRoleModel = ManageAccountRoleModel::getInstance();
        $roles = $ManageAccountRoleModel->getRoleIdsByAccountId($accountInfo["id"]);
        if (empty($roles)) {
            return false;
        }
        $rightsIdArr = ManageRoleModel::getInstance()->getAllRightsIdsByIds($roles, $adminCheck);
        //超级管理员
        if ($adminCheck) {
            return true;
        }
        if (empty($rightsIdArr)) {
            return false;
        }
        $api_url_arr = ManageRightsApiModel::getInstance()->getApiByRightsIds($rightsIdArr);
        if (empty($api_url_arr[$system_id])) {
            return false;
        }
        $api_url_arr = $api_url_arr[$system_id];
        $result = in_array($api_url, $api_url_arr);
        $result2 = in_array("/" . $api_url, $api_url_arr);

        return ($result || $result2) ? true : false;
    }

    /**
     * 获取用户所有的权限标识数组
     * @param $accountInfo
     * @param int $system_id
     * @return array
     */
    public function getAllowRightsFlagArr($accountInfo, $system_id = ManageRightsModel::SYSTEM_MANAGE)
    {
        if (is_numeric($accountInfo)) {
            $accountInfo = $this->getOne($accountInfo);
        }
        //root用户
        if ($accountInfo["is_root"] == self::ROOT_YES) {
            return ["_all"];
        }
        $ManageAccountRoleModel = ManageAccountRoleModel::getInstance();
        $roles = $ManageAccountRoleModel->getRoleIdsByAccountId($accountInfo["id"]);
        if (empty($roles)) {
            return [];
        }
        $rightsIdArr = ManageRoleModel::getInstance()->getAllRightsIdsByIds($roles, $adminCheck);
        //超级管理员
        if ($adminCheck) {
            return ["_all"];
        }
        if (empty($rightsIdArr)) {
            return [];
        }
        $ManageRightsModel = ManageRightsModel::getInstance();
        $flagArr = $ManageRightsModel->getAll([
            "id IN" => $rightsIdArr,
            "system_id" => $system_id,
        ]);
        return array_column($flagArr, "flag");
    }
}