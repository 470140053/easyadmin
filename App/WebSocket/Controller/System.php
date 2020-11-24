<?php

namespace App\WebSocket\Controller;

/**
 * 系统类
 */
class System extends Base
{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [
        'beatHeart'
    ];

    /**
     * 心跳检测
     * @return bool
     */
    public function beatHeart()
    {

        //发送心跳检测
        return $this->success(date('H:i:s') . ' beat heart...');
    }

    /**
     * 历史聊天记录列表
     * @return bool
     * @throws \Exception
     */
    public function historyMessageList()
    {

        $args = $this->getArgs();

        $chatsUid = $args['uid'];

        if (empty($chatsUid)) {
            return $this->error('参数错误!');
        }

        $limit = isset($args['limit']) ? $args['limit'] : 30;

        $msgTime = isset($data['msg_time']) ? $data['msg_time'] : socketHelp()->getMillisecond();

        $uid = $this->getUid();

        $toUserInfo = socketHelp()->userInfo($chatsUid);

        if (empty($toUserInfo) || !is_array($toUserInfo['parsing_has']) || !isset($toUserInfo['parsing_has'][0])) {
            return $this->error('无效的用户，聊天记录获取失败!');
        }

        $chatsId = socketHelp()->systemChatsId($uid, $chatsUid, $toUserInfo['parsing_app_has'][0]);

        $list = \App\WebSocket\Model\Chats\ChatsLogModel::getInstance()->historyMessageList($chatsId, $msgTime, $limit);

        if ($list === false) {
            return $this->error();
        }

        $sendData = [
            'uid'      => $uid,
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

        $args = $this->getArgs();

        $chatsUid = $args['uid'];

        $uid = $this->getUid();

        if (empty($chatsUid)) {
            return $this->error('参数错误!');
        }

        $toUserInfo = socketHelp()->userInfo($chatsUid);

        if (empty($toUserInfo) || !is_array($toUserInfo['parsing_has']) || !isset($toUserInfo['parsing_has'][0])) {
            return $this->error('无效的用户，聊天记录获取失败!');
        }

        $chatsId = socketHelp()->systemChatsId($uid, $chatsUid, $toUserInfo['parsing_app_has'][0]);

        $sendData = [
            'chats_id' => $chatsId,
            'uid'      => $uid
        ];

        return $this->success('ok', $sendData);
    }
}
