<?php

namespace App\Storage;

use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use Swoole\Table;

/**
 * 房间在线用户
 */
class User extends Base
{
    use Singleton;
    protected $table;  // 储存用户信息的Table

    protected $_tableName = 'User';

    /**
     * 字段声明
     * @var array[]
     */
    protected $_columns = [
        'uid'         => ['type' => Table::TYPE_INT, 'size' => 8],
        'has_id'      => ['type' => Table::TYPE_INT, 'size' => 8],
        'has'         => ['type' => Table::TYPE_STRING, 'size' => 32],
        'parsing_has' => ['type' => Table::TYPE_STRING, 'size' => 32],
        'nickname'    => ['type' => Table::TYPE_STRING, 'size' => 32],
        'sign'        => ['type' => Table::TYPE_STRING, 'size' => 255],
        'sex'         => ['type' => Table::TYPE_STRING, 'size' => 32],
        'avatar'      => ['type' => Table::TYPE_STRING, 'size' => 255],
        'update_time' => ['type' => Table::TYPE_INT, 'size' => 10],
        'login_time'  => ['type' => Table::TYPE_INT, 'size' => 10],
    ];

    protected $_columnsKey = [];

    public function __construct()
    {

        TableManager::getInstance()->add($this->_tableName, $this->_columns);

        $this->table = TableManager::getInstance()->get($this->_tableName);
    }

    /**
     * 字段key
     * @return array
     */
    protected function columnsKey()
    {
        if (empty($this->_columnsKey)) {
            $this->_columnsKey = array_keys($this->_columns);
        }
        return $this->_columnsKey;
    }

    /**
     * 格式化数据
     * @param $data
     * @return array
     */
    private function formatData($data)
    {

        $formatData = [];

        $keys = $this->columnsKey();

        foreach ($keys as $key) {
            $formatData[$key] = isset($data[$key]) ? $data[$key] : '';
        }

        if (is_array($formatData['parsing_has'])) {
            $formatData['parsing_has'] = implode(',', $formatData['parsing_has']);
        }

        if (!isset($formatData['update_time'])) {
            $formatData['update_time'] = time();
        }

        return $formatData;
    }

    /**
     *
     * @param $uid
     * @param $data
     * @return mixed
     */
    public function set($uid, $data)
    {
        $data = $this->formatData($data);
        $data['uid'] = $uid;
        return $this->table->set($uid, $data);
    }

    /**
     * 获取一条用户信息
     * @param $uid
     * @return array|mixed|null
     */
    public function get($uid)
    {
        $info = $this->table->get($uid);
        if (!is_array($info)) {
            return null;
        }
        $info['parsing_has'] = !empty($info['parsing_has']) ? explode(',', $info['parsing_has']) : [];
        return $info;
    }

    /**
     * 更新一条用户信息
     * @param $uid
     * @param $data
     */
    public function update($uid, $data)
    {
        $info = $this->get($uid);
        if ($info) {
            $uid = $info['uid'];
            $info = array_merge($info, $data);
            $this->set($uid, $info);
        }
    }

    /**
     * 删除一条用户信息
     * @param $uid
     * @return bool
     */
    public function delete($uid)
    {
        $this->table->del($uid);
        return true;
    }

    /**
     * 删除所有
     */
    public function deleteAll()
    {
        foreach ($this->table as $uid => $userInfo) {
            $this->delete($uid);
        }
    }

    /**
     * 直接获取当前的表
     * @return Table|null
     */
    public function table()
    {
        return $this->table;
    }
}
