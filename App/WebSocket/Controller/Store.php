<?php

namespace App\WebSocket\Controller;


/**
 * 店铺通讯
 */
class Store extends Base
{
    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [
        'joinRoom'
    ];

    /**
     * 不需要验证加入房间的方法
     * @var string[]
     */
    protected $noNeedRoom = [
        'joinRoom'
    ];

    /**
     * 验证是否加入房间
     * @return bool
     */
    protected function _auth()
    {

        if (!$this->match($this->noNeedRoom, $this->getAction())) {
            if ($this->helpRoom()->verifyJoin() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 加入房间
     * @return bool
     */
    public function joinRoom()
    {

        $helpRoom = $this->helpRoom();
        $roomId = $helpRoom->roomId();

        if (empty($roomId)) {
            return $this->error('无效的房间!');
        }

        return $helpRoom->join();
    }

    /**
     * 离开房间
     * @return mixed
     */
    public function outRoom()
    {
        return $this->helpRoom()->out();
    }

    /**
     * 房间发送消息
     * @return bool
     */
    public function roomSend()
    {
        return $this->helpRoom()->send();
    }

    /**
     * 房间用户信息
     * @return bool
     */
    public function roomUserList()
    {
        $list = $this->helpRoom()->mHelp()->roomUserList();
        return $this->success('ok', [
            'user_list'     => $list
        ]);
    }

    /**
     * 历史聊天记录列表
     * @return bool
     * @throws \Exception
     */
    public function historyMessageList()
    {

        $args = $this->getArgs();

        $roomId = $this->helpRoom()->roomId();

        $limit = isset($args['limit']) ? $args['limit'] : 30;

        $msgTime = isset($data['msg_time']) ? $data['msg_time'] : socketHelp()->getMillisecond();

        if (empty($roomId)) {
            return $this->error('参数错误!');
        }

        $chatsId = socketHelp()->roomChatsId($roomId);

        $list = \App\WebSocket\Model\Chats\ChatsLogModel::getInstance()->historyMessageList($chatsId, $msgTime, $limit);

        if ($list === false) {
            return $this->error();
        }

        $sendData = [
            'room_id'  => $roomId,
            'list'     => $list,
            'msg_time' => $msgTime,
            'chats_id' => $chatsId
        ];

        return $this->success('获取成功!', $sendData);
    }

    /**
     * 获取会话ID
     * @return bool
     * @throws \Exception
     */
    public function chatsId()
    {

        $roomId = $this->helpRoom()->roomId();

        $chatsId = socketHelp()->roomChatsId($roomId);

        $sendData = [
            'chats_id' => $chatsId,
            'room_id'  => $roomId
        ];

        return $this->success('ok', $sendData);
    }

    /**
     * 删除房间消息
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function roomDelMessage()
    {

        if ($this->helpRoom()->isAdmin() === false) {
            return false;
        }

        $args = $this->getArgs();

        //消息ID
        $logId = !empty($args['log_id']) ? $args['log_id'] : 0;

        if (empty($logId)) {
            return $this->error('请选择要删除的消息!');
        }

        $roomId = $this->helpRoom()->roomId();

        if (empty($roomId)) {
            return $this->error('无效的房间!');
        }

        $chatsId = socketHelp()->roomChatsId($roomId);

        $model = \App\WebSocket\Model\Chats\ChatsLogModel::getInstance();

        //删除聊天记录
        $del = $model::create()->where(['log_id' => $logId, 'chats_id' => $chatsId])->destroy();

        if (!$del) {
            return $this->error('消息删除失败,或不存在!');
        }

        //设置附加参数
        $data = [
            'room_id'       => $roomId,
            'log_id'        => $logId
        ];

        $pushData = $this->successData('删除消息!', $data);

        //推送删除消息
        return $this->helpRoom()->push($pushData);
    }

    /**
     * 删除用户消息
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function roomDelUserMessage()
    {

        if ($this->helpRoom()->isAdmin() === false) {
            return false;
        }

        $args = $this->getArgs();

        $msgUid = !empty($args['msg_uid']) ? $args['msg_uid'] : 0;
        $msgType = !empty($args['msg_type']) ? $args['msg_type'] : 0; //0 普通消息 1 付费消息 2 全部消息

        if (empty($msgUid)) {
            return $this->error('无效的用户!');
        }

        $roomId = $this->helpRoom()->roomId();

        if (empty($roomId)) {
            return $this->error('无效的房间!');
        }

        $chatsId = socketHelp()->roomChatsId($roomId);

        $model = \App\WebSocket\Model\Chats\ChatsLogModel::getInstance();

        $list = $model::create()->where(['uid' => $msgUid, 'chats_id' => $chatsId])->field(['log_id', 'ext'])->all();

        if (empty($list)) {
            return $this->error('该用户没有可删除消息!');
        }

        $logIds = [];

        switch ($msgType) {
            case 0: //普通消息
                $logIds = array_column($list, 'log_id');
                break;

            case 1: //付费消息
                $logIds = array_column($list, 'log_id');
                break;

            case 2: //全部消息
                $logIds = array_column($list, 'log_id');
                break;
        }

        //删除聊天记录
        $del = $model::create()->where(['log_id' => $logIds])->destroy();

        if (!$del) {
            return $this->error('消息删除失败,或不存在!');
        }

        //设置附加参数
        $data = [
            'room_id' => $roomId,
            'log_ids' => $logIds
        ];

        $pushData = $this->successData('删除消息!', $data);

        //推送删除消息
        return $this->helpRoom()->push($pushData);
    }
}
