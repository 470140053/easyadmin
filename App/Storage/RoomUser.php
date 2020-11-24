<?php

namespace App\Storage;

/**
 * 房间在线用户
 */
class RoomUser extends Base
{

    private static $instance;

    /**
     * @param mixed ...$args
     * @return RoomUser
     */
    public static function getInstance(...$args)
    {
        $key = serialize($args);
        if (!isset(self::$instance[$key])) {
            self::$instance[$key] = new static(...$args);
        }
        return self::$instance[$key];
    }

    const TABLE_NAME = 'socket_roomUser';

    const FD_CONNECT_NAME = 'room';

    protected $table;  // 储存用户信息的Table

    protected $tableName = '';

    protected $_roomId = 0;

    public function __construct($roomId)
    {
        $this->_roomId = $roomId;
        $this->tableName = self::TABLE_NAME;
    }

    /**
     * 房间ID
     * @return int
     */
    public function roomId()
    {
        return $this->_roomId;
    }

    /**
     * 基础table名称
     * @return string
     */
    public static function baseTableName()
    {
        return self::TABLE_NAME . ':';
    }

    /**
     * 表名
     * @return string
     */
    protected function tableName()
    {
        return $this->tableName . ':' . $this->_roomId;
    }

    /**
     * 加入房间
     * @param $fd
     * @param bool $isFirst 是否第一次进入房间的fd
     * @return bool
     */
    public function joinRoom($fd, bool &$isFirst = false)
    {
        $uid = socketHelp()->getUid($fd);
        if (empty($uid)) {
            return returnUtil()->error('无效的用户ID!');
        }

        if (!$this->exist($uid)) {
            $isFirst = true;
            $this->setFd($uid, $fd);
        } else {
            $this->updateFds($fd);
        }
        socketHelp()->fdConnect($fd, [self::FD_CONNECT_NAME => $this->roomId()]);
        return true;
    }

    /**
     * 是否存在
     * @param $uid
     * @return bool|string
     */
    public function exist($uid)
    {
        return eRedis()->hExists($this->tableName(), $uid);
    }

    /**
     * 离开房间
     * @param $fd
     * @param bool $isLast 是否最后一个退出的fd
     * @return bool
     */
    public function outRoom($fd, bool &$isLast = false)
    {
        $uid = socketHelp()->getUid($fd);
        socketHelp()->delFdConnect($fd, self::FD_CONNECT_NAME);
        $ret = $this->updateFds($fd, false);
        if (!$this->exist($uid)) {
            $isLast = true;
        }
        return $ret;
    }

    /**
     * 设置进入信息
     * @param $uid
     * @param $fd
     * @return mixed
     */
    public function setFd($uid, $fd)
    {

        $userInfo = socketHelp()->userInfo($uid);

        return $this->set($uid, $this->resetData([
            'uid'        => $uid,
            'has'        => $userInfo['has'],
            'has_id'     => $userInfo['has_id'],
            'join_time'  => time(),
            'speak_time' => 0,
            'fds'        => [$fd]
        ]));
    }

    /**
     * 设置信息
     * @param $uid
     * @param $data
     * @return mixed
     */
    public function set($uid, $data)
    {
        return eRedis()->hSet($this->tableName(), $uid, $this->resetData($data));
    }

    /**
     * 获取一条用户信息
     * @param $uid
     * @return array|mixed|null
     */
    public function get($uid)
    {
        $info = eRedis()->hGet($this->tableName(), $uid);
        if (!empty($info)) {
            $info = $this->resetData($info, false);
        }
        return is_array($info) ? $info : null;
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
            $info = array_merge($info, $data);
            $this->set($uid, $info);
        }
    }

    /**
     * 更新fd
     * @param $fd
     * @param bool $isAdd
     * @return bool
     */
    public function updateFds($fd, bool $isAdd = true)
    {

        $uid = socketHelp()->getUid($fd);
        if (empty($uid)) {
            return returnUtil()->error('无效的用户ID!');
        }

        $info = $this->get($uid);

        if (empty($info)) {
            return returnUtil()->error('该用户未加入房间!');
        }

        //更新
        $fds = !empty($info['fds']) ? $info['fds'] : [];
        if ($isAdd) {
            $fds[] = $fd;
            $fds = array_filter(array_unique($fds));
        } else {
            $key = array_search($fd, $fds);
            if ($key !== false) {
                //移除fd
                array_splice($fds, $key, 1);
            }
        }

        if (empty($fds)) {
            return $this->delete($uid);
        }

        $info['fds'] = $fds;
        $this->set($uid, $info);
        return true;
    }

    /**
     * 删除一条用户信息
     * @param $uid
     * @return bool
     */
    public function delete($uid)
    {
        eRedis()->hDel($this->tableName(), $uid);
        return true;
    }

    /**
     * 获取所有key
     * @return bool|string
     */
    public function getKeys()
    {
        return eRedis()->hKeys($this->tableName());
    }

    /**
     * 获取所有数据
     * @return bool|string
     */
    public function getAll()
    {
        return eRedis()->hGetAll($this->tableName());
    }
}
