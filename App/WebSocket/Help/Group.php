<?php

/**
 * 群组帮助文件
 */

namespace App\WebSocket\Help;


class Group extends Base
{

    /**
     * 推送到群组
     * @param $groupArr
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function push($groupArr, $data)
    {

        is_array($groupArr) || $groupArr = explode(',', $groupArr);

        $groupArr = array_unique(array_filter($groupArr));

        if (empty($groupArr)) {
            return $this->error('群ID错误');
        }

        foreach ($groupArr as $groupId) {

            $membersList = socketHelp()->groupMembersList($groupId);

            $pushUid = array_column($membersList, 'uid');

            socketHelp()->pushUid($pushUid, $data);
        }

        return true;
    }

    /**
     * 是否是群组成员
     * @param $groupId 群组ID
     * @param int $uid
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function isMembers($groupId, $uid = 0)
    {
        if (empty($uid)) {
            $uid = $this->getUid();
        }
        if (!\App\WebSocket\Model\Chats\ChatsGroupMembersModel::getInstance()->where(['group_id' => $groupId, 'uid' => $uid])->count()) {
            return $this->error('你不是该群成员!');
        }
        return true;
    }

    /**
     * 消息
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function message()
    {

        $args = $this->getArgs();

        if (empty($args['to'])) {
            return $this->error('非法访问!');
        }

        $groupId = $args['to'];

        $userInfo = $this->userInfo();
        $uid = $userInfo['uid'];

        if (!$this->isMembers($groupId)) {
            return false;
        }

        $data = $args;

        $params = [
            'type'      => isset($data['type']) ? $data['type'] : '',
            'ext'       => isset($data['ext']) ? $data['ext'] : ''
        ];

        $time = !empty($data['time']) ? $data['time'] : socketHelp()->getMillisecond();

        $data['time'] = $time;

        //添加聊天记录
        $addLog = socketHelp()->addChatsGroupLog($uid, $groupId, $data['content'], $time, $params);

        if ($addLog === false) {
            return $this->error();
        }

        $chatsId = socketHelp()->groupChatsId($uid, $groupId);

        //设置附加参数
        $this->setExtendData([
            'group_id'  => $groupId,
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

        $this->push($groupId, $pushData);

        //回传消息
        return true;
    }
}
