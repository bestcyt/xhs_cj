<?php
/**
 * 系统权限管理
 */

namespace Mt\Model\Manage;

use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

class ManageRightsModel extends Model
{
    use InstanceTrait;
    use TableTrait;

    const SYSTEM_MANAGE = 1;//管理后台
    const SYSTEM = [
        self::SYSTEM_MANAGE => "管理后台",
    ];
    const SYSTEM_MAP = [
        "Manage" => self::SYSTEM_MANAGE,
    ];

    //状态
    const STATUS_NORMAL = 1;
    const STATUS_FORBID = 2;
    const STATUS = [
        self::SYSTEM_MANAGE => "启用",
        self::STATUS_FORBID => "禁用",
    ];

    protected function __construct()
    {
        $this->table = 'rights';
        $this->dbGroup = $this->getMainDbGroup();
    }

    /**
     * 获取当前系统id
     * @return mixed
     */
    public static function getCurrentSystemId()
    {
        $currentAppName = currentAppName();
        return self::SYSTEM_MAP[$currentAppName];
    }

    public function getFirstLevelRights($system_id, array $rightsFlagArr)
    {
        $rows = $this->db()->select()->from($this->table)->multiWhere([
            "system_id" => $system_id,
            "level <=" => 2,
        ])->orderBy("level,left_value")->fetchAll();
        $result = [];
        foreach ($rows as $value) {
            if ($value["level"] == 1) {
                $value["first_child_front_url"] = "";
                $result[$value["id"]] = $value;
            } else {
                if (empty($result[$value["parent_id"]]["first_child_front_url"]) && in_array($value["flag"], $rightsFlagArr)) {
                    $result[$value["parent_id"]]["first_child_front_url"] = $value["front_url"];
                }
            }
        }
        return array_values($result);
    }

    public function getRightsBySystemId($system_id)
    {
        return $this->db()->select()->from($this->table)->multiWhere([
            "system_id" => $system_id,
        ])->fetchAll();
    }

    public function getRightsIdArrBySystemId($system_id)
    {
        $rows = $this->db()->select("id")->from($this->table)->multiWhere([
            "system_id" => $system_id,
        ])->fetchAll();
        return array_column($rows, "id");
    }

    public function getTree($where)
    {
        $children = $this->db()->select("*")->multiWhere($where)->from($this->table)->orderBy("left_value")->fetchAll();
        $children = $this->_recycle_result(0, $children);
        return $children;
    }

    protected function _recycle_result($parent_id, &$array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            if ($value["parent_id"] == $parent_id) {
                $value['children'] = $this->_recycle_result($value["id"], $array);
                $result[] = $value;
            }
        }
        return $result;
    }

    public function addNode($system_id, $parent_id, array $data)
    {
        $where = [
            "system_id" => $system_id,
        ];
        $target_node = false;
        if (is_numeric($parent_id)) {
            $target_node = $this->getOne($parent_id);
        }
        //没有父级
        if (!$target_node) {
            $data["parent_id"] = 0;
            $data["level"] = 1;
            $max_right = $this->getMaxField("right_value", $where);
            $data["left_value"] = $max_right + 1;
            $data["right_value"] = $max_right + 2;
            $result = $this->insert($data);
            return $result;
        }
        //更改
        $this->db()->beginTrans();
        $result = $this->db()->multiWhere([
            "system_id" => $system_id,
            "left_value >" => $target_node["right_value"],
        ])->update($this->table, [
            "left_value=left_value+2"
        ])->exec();
        if (empty($result)) {
            $this->db()->rollbackTrans();
            return false;
        }
        $result = $this->db()->multiWhere([
            "system_id" => $system_id,
            "right_value >=" => $target_node["right_value"],
        ])->update($this->table, [
            "right_value=right_value+2"
        ])->exec();
        if (empty($result)) {
            $this->db()->rollbackTrans();
            return false;
        }
        //增加
        $data["parent_id"] = $target_node["id"];
        $data["level"] = $target_node["level"] + 1;
        $data["left_value"] = $target_node["right_value"];
        $data["right_value"] = $target_node["right_value"] + 1;
        $result = $this->insert($data);
        if (empty($result)) {
            $this->db()->rollbackTrans();
            return false;
        }
        $this->db()->commitTrans();
        return $result;
    }

    protected function getMaxField($field, array $where)
    {
        $row = $this->db()->from($this->table)->multiWhere($where)->select("max({$field}) as max_value")->fetch();
        return $row ? intval($row["max_value"]) : 0;
    }

    public function moveBeforeNode($target_node)
    {
        if (is_numeric($target_node)) {
            $target_node = $this->getOne($target_node);
        }
        $prev_node = $this->db()->select("*")->from($this->table)->multiWhere([
            "system_id" => $target_node["system_id"],
            "parent_id" => $target_node["parent_id"],
            "right_value" => $target_node["left_value"] - 1,
        ])->fetch();
        if ($prev_node) {
            $prev_number = $prev_node["right_value"] - $prev_node["left_value"];
            $target_number = $target_node["right_value"] - $target_node["left_value"];
            $prev_move = $target_number + 1;
            $target_move = $prev_number + 1;
            //移动项id值
            $target_row = $this->db()->select("id")->from($this->table)->multiWhere([
                "system_id" => $target_node["system_id"],
                "left_value >=" => $target_node["left_value"],
                "right_value <=" => $target_node["right_value"],
            ])->fetchAll();
            $target_id = array_column($target_row, "id");
            //移动
            $this->db()->beginTrans();
            $result = $this->db()->multiWhere([
                "id IN" => $target_id,
            ])->update($this->table, [
                "left_value=left_value-" . $target_move,
                "right_value=right_value-" . $target_move,
            ])->exec();
            if (empty($result)) {
                $this->db()->rollbackTrans();
                return false;
            }
            $result = $this->db()->multiWhere([
                "system_id" => $target_node["system_id"],
                "id NOT IN" => $target_id,
                "left_value >=" => $prev_node["left_value"],
                "right_value <=" => $prev_node["right_value"],
            ])->update($this->table, [
                "left_value=left_value+" . $prev_move,
                "right_value=right_value+" . $prev_move,
            ])->exec();
            if (empty($result)) {
                $this->db()->rollbackTrans();
                return false;
            }
            $this->db()->commitTrans();
        }
        return true;
    }

    public function moveAfterNode($target_node)
    {
        if (is_numeric($target_node)) {
            $target_node = $this->getOne($target_node);
        }
        $next_node = $this->db()->select("*")->from($this->table)->multiWhere([
            "system_id" => $target_node["system_id"],
            "parent_id" => $target_node["parent_id"],
            "left_value" => $target_node["right_value"] + 1,
        ])->fetch();
        if ($next_node) {
            $next_number = $next_node["right_value"] - $next_node["left_value"];
            $target_number = $target_node["right_value"] - $target_node["left_value"];
            $next_move = $target_number + 1;
            $target_move = $next_number + 1;
            //移动项id值
            $target_row = $this->db()->select("id")->from($this->table)->multiWhere([
                "system_id" => $target_node["system_id"],
                "left_value >=" => $target_node["left_value"],
                "right_value <=" => $target_node["right_value"],
            ])->fetchAll();
            $target_id = array_column($target_row, "id");
            //移动
            $this->db()->beginTrans();
            $result = $this->db()->multiWhere([
                "id IN" => $target_id
            ])->update($this->table, [
                "left_value=left_value+" . $target_move,
                "right_value=right_value+" . $target_move,
            ])->exec();
            if (empty($result)) {
                $this->db()->rollbackTrans();
                return false;
            }
            $result = $this->db()->multiWhere([
                "system_id" => $target_node["system_id"],
                "id NOT IN" => $target_id,
                "left_value >=" => $next_node["left_value"],
                "right_value <=" => $next_node["right_value"],
            ])->update($this->table, [
                "left_value=left_value-" . $next_move,
                "right_value=right_value-" . $next_move,
            ])->exec();
            if (empty($result)) {
                $this->db()->rollbackTrans();
                return false;
            }
            $this->db()->commitTrans();
        }
        return true;
    }

    public function deleteNode($target_node)
    {
        if (is_numeric($target_node)) {
            $target_node = $this->getOne($target_node);
        }
        $this->db()->beginTrans();
        //删除
        $result = $this->db()->multiWhere([
            "system_id" => $target_node["system_id"],
            "left_value >=" => $target_node["left_value"],
            "right_value <=" => $target_node["right_value"],
        ])->delete($this->table)->exec();
        if (empty($result)) {
            $this->db()->rollbackTrans();
            return false;
        }
        //修改
        $plus_number = $target_node["right_value"] - $target_node["left_value"] + 1;
        $result = $this->db()->multiWhere([
            "system_id" => $target_node["system_id"],
            "left_value >=" => $target_node["left_value"],
        ])->update($this->table, [
            "left_value=left_value-" . $plus_number
        ])->exec();
        if (empty($result)) {
            $this->db()->rollbackTrans();
            return false;
        }
        $result = $this->db()->multiWhere([
            "system_id" => $target_node["system_id"],
            "right_value >=" => $target_node["right_value"],
        ])->update($this->table, [
            "right_value=right_value-" . $plus_number
        ])->exec();
        if (empty($result)) {
            $this->db()->rollbackTrans();
            return false;
        }
        $this->db()->commitTrans();
        return true;
    }


}