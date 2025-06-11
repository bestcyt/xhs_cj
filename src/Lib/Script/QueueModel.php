<?php

namespace Mt\Lib\Script;

use Fw\App;
use Fw\InstanceTrait;
use Fw\TableTrait;
use Mt\Model\Model;

class QueueModel extends Model
{
    use TableTrait {
        insert as private _insert;
        update as private _update;
        delete as private _delete;
        getOne as private _getOne;
        getMulti as private _getMulti;
    }
    use InstanceTrait;

    const STATUS_ON = 1;//执行
    const STATUS_OFF = 0;//停止
    const STATUS_DELETED = 2;//下线

    protected function __construct()
    {
        $this->table = 'queue';
        $this->dbGroup = $this->getScriptDbGroup();
    }

    /**
     * 分页查询队列
     * @param array $options
     * @return mixed
     */
    public function load($options = [])
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $where = ["pool" => $pool];
        $where = array_merge($where, $options);
        $data = $this->db()->multiWhere($where)->from($this->table)
            ->orderBy("created_at desc")
            ->select("*")->fetchAll();

        return preKey($data, 'id');
    }

    //获取队列数量
    public function getTotalCount($options = [])
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $where = ["pool" => $pool];
        $where = array_merge($where, $options);
        $data = $this->db()->from($this->table)
            ->select("count(*) as total")
            ->multiWhere($where)
            ->fetch();

        return $data ? $data['total'] : 0;
    }

    //获取一个队列信息

    public function getMultiByFile($files)
    {
        if (!$files) return [];
        $files = is_array($files) ? $files : [$files];
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $data = $this->db()->from($this->table)->select("*")->multiWhere([
            "pool" => $pool,
            "file IN" => $files
        ])->fetchAll();
        return preKey($data, 'file');
    }

    //批量获取多个队列信息

    public function insertMulti($data)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        foreach ($data as $k => $_data) {
            $data[$k]['created_at'] = App::getCurrentTime();
            $data[$k]['pool'] = $pool;
        }
        return $this->db()->insertBatch($this->table, $data)->exec();
    }

    //获取队列信息

    public function insertOrUpdate($data)
    {
        if (isset($data['id']) && $data['id']) {
            $config_data = $this->getOne($data['id']);
            if ($config_data) {
                $result = $this->update($data['id'], $data);
                return $result;
            }
        }

        $result = $this->insert($data);
        return $result;
    }

    //添加一个队列

    public function getOne($queue_id)
    {
        $data = $this->getMulti($queue_id);
        return $data[$queue_id] ? $data[$queue_id] : array();
    }

    //添加一个队列

    public function getMulti($queue_ids)
    {
        if (!$queue_ids) return [];
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $queue_ids = is_array($queue_ids) ? $queue_ids : [$queue_ids];
        $data = $this->db()->from($this->table)->select("*")->multiWhere([
            "id IN" => $queue_ids,
            "pool" => $pool,
        ])->fetchAll();
        return preKey($data, 'id');
    }

    //更新一个队列

    public function update($queue_id, $data)
    {
        return $this->db()->where("id", $queue_id)->update($this->table, $data)->exec();
    }

    //更新多个队列

    public function insert($data)
    {
        $pool = App::getInstance()->getEnvironment();
        if (isDevelop()) {
            $pool = "pre";
        }
        $data['created_at'] = App::getCurrentTime();
        $data['pool'] = $pool;
        return $this->db()->insert($this->table, $data)->exec();
    }

    //插入/更新一个队列

    public function delete($demo_id)
    {
        return $this->db()->where("id", $demo_id)->delete($this->table)->exec();
    }

    //删除一个队列

    public function changeQueueStatus($queue_ids, $status)
    {
        $queue_ids = is_array($queue_ids) ? $queue_ids : splitNumber($queue_ids);
        $update_data['status'] = $status == -1 ? 1 : $status;
        $result = $this->updateMulti($queue_ids, $update_data);
        if (!$result) {
            return false;
        }

        //更新调度信息
        $QueueDispatchModel = QueueDispatchModel::getInstance();
        if (in_array($status, [-1, self::STATUS_OFF, self::STATUS_DELETED])) {//重启、关闭、下线先删除调度
            $QueueDispatchModel->deleteByQueueIds($queue_ids);
        }
        if (in_array($status, [-1, self::STATUS_ON])) {//重启、开启需更新调度信息
            foreach ($queue_ids as $queue_id) {
                $QueueDispatchModel->updateQueueDispatchByNum($queue_id);
            }
        }
        if (in_array($status, [self::STATUS_OFF, self::STATUS_DELETED])) {//关闭、下线需删除调度信息
            //TODO: 更新监测信息
        }
        return true;
    }

    //-----------------------------------功能函数-------------------------------------------

    //更改队列状态

    public function updateMulti($queue_ids, $data)
    {
        $queue_ids = is_array($queue_ids) ? $queue_ids : splitNumber($queue_ids);
        if (empty($queue_ids)) return true;
        return $this->db()->where("id", $queue_ids, "IN")->update($this->table, $data)->exec();
    }

    /**
     * 用文件查找并返回队列实例
     * @param $file
     * @return bool|mixed|\Mt\Lib\Script\Queue
     */
    public function findAndReturnQueue($file)
    {
        $file = app_root_path() . "/src/App/Script/Queue/" . $file . ".php";
        if (!file_exists($file)) {
            return false;
        }
        $content = file_get_contents($file);
        preg_match_all("/\\\Mt\\\Queue\\\(.*?)Queue\:\:getInstance\(\)/", $content, $matches);
        if (empty($matches[0][0])) {
            return false;
        }
        $class = $matches[0][0];
        $class = str_replace("::getInstance()", "", $class);
        return $class::getInstance();
    }

}