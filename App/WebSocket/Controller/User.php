<?php

namespace App\WebSocket\Controller;

/**
 * Class User
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class User extends Base
{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 获取详情
     * @return bool
     */
    public function getInfo()
    {

        $userInfo = $this->userInfo();

        if (empty($userInfo)) {
            return $this->error('没有用户信息!');
        }

        $args['user_info'] = $userInfo;

        return $this->success('ok', $args);
    }

    /**
     * 发送消息
     * @return bool
     * @throws \Exception
     */
    public function message()
    {

        return $this->helpUser()->message();
    }

    /**
     * 撤回消息
     * @return bool
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

        $chatUid = \App\WebSocket\Model\Chats\ChatsIdModel::getInstance()->parsingText($uid, $info['chats_id'], 'user');

        if (empty($chatUid)) {
            return $this->error('撤回消息失败!');
        }

        $pushUid = [
            $uid,
            $chatUid
        ];

        //推送撤回消息
        $this->helpUser()->push($pushUid, ['info' => $info]);

        return true;
    }

    /**
     * 推送正在输入
     * @return bool
     */
    public function isInput()
    {

        $args = $this->getArgs();

        $toUid = isset($args['to_uid']) ? $args['to_uid'] : $args['to'];

        if (empty($toUid)) {
            return $this->error('非法访问!');
        }

        $uid = $this->getUid();

        $sendData = [
            'uid'    => $uid,
            'status' => $args['status'] ? true : false, //正在输入 结束输入
        ];

        //推送正在输入消息
        $this->helpUser()->push($toUid, $sendData);

        return true;
    }

    /**
     * 获取指定用户信息
     * @return bool
     */
    public function getUserInfo()
    {

        $args = $this->getArgs();

        //需要获得用户数据的uid
        $uid = $args['uid'];

        if (empty($uid)) {
            return $this->error('参数错误!');
        }

        $userInfo = socketHelp()->userInfo($uid);

        if (empty($userInfo)) {
            return $this->error('无效的用户!');
        }

        $onlineStatus = socketHelp()->uidOnlineStatus($userInfo['uid']) ? true : false;

        $sendData = [
            'user_info'      => [
                'uid'        => $userInfo['uid'],
                'nickname'   => $userInfo['nickname'],
                'avatar'     => $userInfo['avatar'],
                'sign'       => $userInfo['sign'],
                'online'     => $onlineStatus,
                'login_time' => $userInfo['login_time']
            ]
        ];

        return $this->success('ok', $sendData);
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

        $chatsId = socketHelp()->userChatsId($uid, $chatsUid);

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

        if (empty($toUserInfo)) {
            return $this->error('无效的用户!');
        }

        $chatsId = socketHelp()->userChatsId($uid, $chatsUid);

        $sendData = [
            'chats_id' => $chatsId,
            'uid'      => $uid
        ];

        return $this->success('ok', $sendData);
    }
}
