<?php

/**
 * 房间帮助文件
 */

namespace App\WebSocket\Help;

use App\Storage\RoomUser;

class Room extends Base
{

    /**
     * 模块帮助方法
     * @return \App\Common\Socket\Room|bool
     */
    public function mHelp()
    {
        if (!is_null($this->_mHelp)) {
            return $this->_mHelp;
        }
        $roomId = $this->roomId();
        if (empty($roomId)) {
            return $this->error('无效的房间!');
        }
        $this->_mHelp = socketHelp()->roomHelp($roomId);
        return $this->_mHelp;
    }

    /**
     * 推送房间消息
     * @param $data
     * @return bool
     */
    public function push($data)
    {

        $help = $this->mHelp();
        if ($help === false) {
            return false;
        }

        $ret = $help->push($data);

        if ($ret === false) {
            return $this->error();
        }

        return $ret;
    }

    /**
     * 获取房间信息
     * @return array|bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getRoomData()
    {
        $roomId = $this->roomId();
        if (empty($roomId)) {
            return $this->error('无效的房间!');
        }

        $help = $this->mHelp();

        if ($help === false) {
            return false;
        }

        $roomData = $help->getRoomData();

        if ($roomData === false) {
            return $this->error();
        }

        return $roomData;
    }

    /**
     * 房间ID
     * @return int|mixed
     */
    public function roomId()
    {
        $roomId = socketHelp()->fdGetRoomId($this->getFd());
        if (!empty($roomId)) {
            return $roomId;
        }

        $args = $this->getArgs();

        return !empty($args['room_id']) ? $args['room_id'] : 0;
    }

    /**
     * 存储对象
     * @return RoomUser|bool
     */
    private function storageRoomUser()
    {
        $help = $this->mHelp();
        if ($help === false) {
            return false;
        }
        return $help->storage();
    }

    /**
     * 获取房间所有用户
     * @param bool $getInfo 获取info信息
     * @return array
     */
    public function getRoomUserAll(bool $getInfo = false)
    {
        $help = $this->mHelp();
        if ($help === false) {
            return [];
        }

        return $help->getRoomUserAll($getInfo);
    }

    /**
     * 加入房间
     * @return bool
     */
    public function join()
    {

        if ($this->autoReg() === false) {
            return false;
        }

        $roomData = $this->getRoomData();

        //获取房间信息
        if ($roomData === false) {
            return false;
        }

        $fd = $this->getFd();

        if ($this->mHelp()->verifyFdJoin($fd)) {
            return $this->error('请勿重复加入房间!');
        }

        //fd是否第一次加入
        $isFirst = false;

        if ($this->storageRoomUser()->joinRoom($fd, $isFirst) === false) {
            return $this->error();
        }

        $args = $this->getArgs();

        $args['user_info'] = $this->userInfo();
        $args['room_id'] = $this->roomId();

        $this->setExtendData([
            'room_data' => $roomData
        ]);

        if ($isFirst) {
            //第一次 推送所有人
            $data = $this->successData('加入房间!', $args);
            $this->push($data);
        } else {
            //仅推送当前fd
            $this->success('加入房间!', $args);
        }

        return true;
    }

    /**
     * 离开房间
     * @return bool
     */
    public function out()
    {

        //fd是否最后一次退出
        $isLast = false;

        if ($this->storageRoomUser()->outRoom($this->getFd(), $isLast) === false) {
            return $this->error('离开房间失败!');
        }

        $args = $this->getArgs();

        $args['user_info'] = $this->userInfo();
        $args['room_id'] = $this->roomId();

        if ($isLast) {
            $data = $this->successData('离开房间!', $args);
            return $this->push($data);
        } else {
            //仅推送当前fd
            $this->success('离开房间!', $args);
        }

        return true;
    }

    /**
     * 验证是否加入房间
     * @return bool
     */
    public function verifyJoin()
    {

        $help = $this->mHelp();
        if ($help === false) {
            return false;
        }

        $fd = $this->getFd();

        $ret = $help->verifyFdJoin($fd);
        if ($ret === false) {
            return $this->error();
        }

        return $ret;
    }

    /**
     * 发送房间消息
     * @return bool
     */
    public function send()
    {

        return $this->message();
    }

    /**
     * 发送房间消息
     * @return bool
     */
    public function message()
    {

        $args = $this->getArgs();

        $roomId = $this->roomId();

        if (empty($roomId)) {
            return $this->error('非法访问!');
        }

        $userInfo = $this->userInfo();
        $uid = $userInfo['uid'];

        $data = $args;

        $params = [
            'type'      => isset($data['type']) ? $data['type'] : '',
            'ext'       => isset($data['ext']) ? $data['ext'] : ''
        ];

        $time = !empty($data['time']) ? $data['time'] : socketHelp()->getMillisecond();

        $data['time'] = $time;

        //添加聊天记录
        $addLog = socketHelp()->addChatsGroupLog($uid, $roomId, $data['content'], $time, $params);

        if ($addLog === false) {
            return $this->error();
        }

        $chatsId = socketHelp()->roomChatsId($uid, $roomId);

        //设置附加参数
        $this->setExtendData([
            'room_id'   => $roomId,
            'from'      => $uid,
            'from_data' => [
                'has'      => $userInfo['has'],
                'nickname' => $userInfo['nickname'],
                'avatar'   => $userInfo['avatar']
            ],
            'msg_id'    => $addLog,
            'chats_id'  => $chatsId
        ]);

        $pushData = $this->successData('ok', $data);

        $this->push($pushData);

        //回传消息
        return true;
    }


    /**
     * 是否是管理员
     * @return bool
     */
    public function isAdmin()
    {

        $userInfo = $this->userInfo();

        if (empty($userInfo)) {
            return $this->error('登录失效!');
        }

        if ($userInfo['has'] != 'store' || $userInfo['has_id'] != $this->roomId()) {
            return $this->error('您没有管理员权限!');
        }

        return true;
    }
}
