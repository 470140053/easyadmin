<?php

namespace App\WebSocket\Controller;


/**
 * 群组类
 */
class Group extends Base
{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 消息
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function message()
    {

        return $this->helpGroup()->message();
    }

    /**
     * 撤回消息
     * @return bool
     * @throws \Exception
     */
    public function withdrawMessage()
    {

        $args = $this->getArgs();

        if (empty($args['msg_id'])) {
            return $this->error('参数错误!');
        }

        $uid = $this->getUid();
        $msgId = $args['msg_id'];

        $model = \App\WebSocket\Model\Chats\ChatsLogModel::getInstance();

        $info = $model->withdrawMessage(['log_id' => $msgId, 'uid' => $uid]);

        if ($info === false) {
            return $this->error($model->getError(), $model->getCode());
        }

        $info['msg_id'] = $info['log_id'];

        $groupId = \App\WebSocket\Model\Chats\ChatsIdModel::getInstance()->parsingText($uid, $info['chats_id'], 'group');

        if (empty($groupId)) {
            return $this->error('撤回消息失败!');
        }

        //推送撤回消息
        $this->helpGroup()->push($groupId, ['info' => $info]);

        return true;
    }

    /**
     * 历史聊天记录列表
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function historyMessageList()
    {

        $args = $this->getArgs();

        $groupId = !empty($args['groupId']) ? $args['groupId'] : '';

        $limit = isset($args['limit']) ? $args['limit'] : 30;

        $msgTime = isset($data['msg_time']) ? $data['msg_time'] : socketHelp()->getMillisecond();

        if (empty($groupId)) {
            return $this->error('参数错误!');
        }

        if (!$this->helpGroup()->isMembers($groupId)) {
            return false;
        }

        $chatsId = socketHelp()->groupChatsId($groupId);

        $list = \App\WebSocket\Model\Chats\ChatsLogModel::getInstance()->historyMessageList($chatsId, $msgTime, $limit);

        if ($list === false) {
            return $this->error();
        }

        $sendData = [
            'group_id' => $groupId,
            'list'     => $list,
            'msg_time' => $msgTime,
            'chats_id' => $chatsId
        ];

        return $this->success('获取成功!', $sendData);
    }

    /**
     * 创建群聊
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function createGroup()
    {

        $args = $this->getArgs();

        $groupName = isset($args['group_name']) ? $args['group_name'] : '';

        if (empty($groupName)) {
            return $this->error('请输入群聊名称!');
        }

        $uid = $this->getUid();

        $groupId = socketHelp()->createGroup($uid, $groupName);

        if ($groupId === false) {
            return $this->error();
        }

        $groupInfo = \App\WebSocket\Model\Chats\ChatsGroupModel::getInstance()->getGroupInfo($groupId);

        $sendData = [
            'group_info'    => $groupInfo
        ];

        //推送创建群聊消息
        $this->helpUser()->push($uid, $sendData);

        return true;
    }

    /**
     * 加入群聊
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function addGroupMembers()
    {

        $args = $this->getArgs();

        $groupId = isset($args['group_id']) ? $args['group_id'] : '';

        if (empty($groupId)) {
            return $this->error('群ID错误!');
        }

        $uid = $this->getUid();

        $membersId = socketHelp()->addGroupMembers($groupId, $uid);

        if ($membersId === false) {
            return $this->error();
        }

        $groupInfo = \App\WebSocket\Model\Chats\ChatsGroupModel::getInstance()->getGroupInfo($groupId);

        $sendData = [
            'group_info'     => $groupInfo,
            'user_info'      => socketHelp()->userInfo($uid)
        ];

        //推送加入群聊消息
        $this->helpGroup()->push($groupId, $sendData);

        return true;
    }

    /**
     * 获取会话ID
     * @return bool
     * @throws \Exception
     */
    public function chatsId()
    {

        $args = $this->getArgs();

        $groupId = isset($args['groupId']) ? $args['groupId'] : '';

        if (empty($groupId)) {
            return $this->error('参数错误!');
        }

        $chatsId = socketHelp()->groupChatsId($groupId);

        $sendData = [
            'chats_id'       => $chatsId,
            'group_id'       => $groupId
        ];

        return $this->success('ok', $sendData);
    }
}
