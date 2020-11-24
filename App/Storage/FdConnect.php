<?php


namespace App\Storage;

use EasySwoole\Component\Singleton;
use EasySwoole\Component\TableManager;
use Swoole\Table;

/**
 * fd关联数据
 * Class FdConnect
 * @package App\Storage
 */
class FdConnect extends Base
{

    use Singleton;
    protected $table;  // 储存用户信息的Table

    protected $_tableName = 'FdConnect';

    /**
     * 字段声明
     * @var array[]
     */
    protected $_columns = [
        'fd'         => ['type' => Table::TYPE_INT, 'size' => 8],
        'connect'    => ['type' => Table::TYPE_STRING, 'size' => 200]
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

        return $formatData;
    }

    /**
     * @param $fd
     * @param array $arr
     * @return bool
     */
    public function connect($fd, array $arr)
    {
        if (!$this->table()->exist($fd)) {
            $this->set($fd, [
                'connect'   => $arr
            ]);
        } else {
            $this->updateConnect($fd, $arr);
        }

        return true;
    }

    /**
     * 删除关联
     * @param $fd
     * @param string $arrKey
     * @return bool
     */
    public function delConnect($fd, string $arrKey)
    {
        if ($this->table()->exist($fd)) {
            $info = $this->get($fd);
            unset($info['connect'][$arrKey]);
            if (empty($info['connect'])) {
                $this->delete($fd);
            } else {
                $this->update($fd, $info);
            }
        }

        return true;
    }

    /**
     * 获取连接信息
     * @param $fd
     * @return array|mixed
     */
    public function getConnect($fd)
    {
        $info = $this->get($fd);
        return !is_null($info) ? $info['connect'] : [];
    }

    /**
     *
     * @param $fd
     * @param $data
     * @return mixed
     */
    public function set($fd, $data)
    {
        $data = $this->formatData($data);
        $data['fd'] = $fd;
        $data['connect'] = $this->resetData($data['connect']);
        return $this->table->set($fd, $data);
    }

    /**
     * 获取一条用户信息
     * @param $fd
     * @return array|mixed|null
     */
    public function get($fd)
    {
        $info = $this->table->get($fd);
        if (is_array($info)) {
            $info['connect'] = $this->resetData($info['connect'], false);
            return $info;
        } else {
            return null;
        }
    }

    /**
     * 更新连接信息
     * @param $fd
     * @param array $arr
     */
    public function updateConnect($fd, array $arr)
    {
        $info = $this->get($fd);
        $info['connect'] = array_merge($info['connect'], $arr);
        return $this->update($fd, $info);
    }

    /**
     * 更新一条用户信息
     * @param $fd
     * @param $data
     */
    public function update($fd, $data)
    {
        $info = $this->get($fd);
        if ($info) {
            $fd = $info['fd'];
            $info = array_merge($info, $data);
            $this->set($fd, $info);
        }
    }

    /**
     * 删除一条用户信息
     * @param $fd
     * @return bool
     */
    public function delete($fd)
    {
        $this->table->del($fd);
        return true;
    }

    /**
     * 删除所有
     */
    public function deleteAll()
    {
        foreach ($this->table as $fd => $userInfo) {
            $this->delete($fd);
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
