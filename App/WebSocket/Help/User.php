<?php

/**
 * 用户帮助文件
 */

namespace App\WebSocket\Help;


class User extends Base
{

    /**
     * 模块帮助方法
     * @return \App\Common\Socket\User|null
     */
    public function mHelp()
    {
        if (!is_null($this->_mHelp)) {
            return $this->_mHelp;
        }

        $this->_mHelp = socketHelp()->userHelp();
        return $this->_mHelp;
    }

    /**
     * 推送到uid
     * @param $uids
     * @param $data
     * @return bool
     */
    public function push($uids, $data)
    {
        $help = $this->mHelp();

        $ret = $help->push($uids, $data);

        if ($ret === false) {
            return $this->error();
        }

        return $ret;
    }

    /**
     * 发送消息
     * @return bool
     * @throws \Exception
     */
    public function message()
    {

        $uid = $this->getUid();

        $userInfo = $this->userInfo();

        $args = $this->getArgs();

        //兼容参数 to_uid to
        $toUid = isset($args['to_uid']) ? $args['to_uid'] : $args['to'];
        if (empty($toUid)) {
            return $this->error('发送人错误!');
        }

        if ($uid == $toUid) {
            return $this->error('不能给自己发消息!');
        }

        $data = $args;

        $params = [
            'type' => isset($data['type']) ? $data['type'] : '',
            'ext'  => isset($data['ext']) ? $data['ext'] : ''
        ];

        if ($args['content'] == '' && $params['type'] == '' && $params['ext'] == '') {
            return $this->error('不能发送空消息!');
        }

        $time = !empty($data['time']) ? $data['time'] : socketHelp()->getMillisecond();

        $data['time'] = $time;

        //添加聊天记录
        $addLog = socketHelp()->addChatsUserLog($uid, $toUid, $data['content'], $time, $params);

        if ($addLog === false) {
            return $this->error();
        }

        $chatsId = socketHelp()->userChatsId($uid, $toUid);

        //设置附加参数
        $this->setExtendData([
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

        $this->push([$uid, $toUid], $pushData);

        //回传消息
        return true;
    }
}
