<?php

namespace App\WebSocket\Controller;


/**
 * info类
 */
class Info extends Base
{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 会话列表
     * @return bool
     * @throws \Exception
     */
    public function chatsMessageList()
    {

        $uid = $this->getUid();

        $loadList = false;

        if (isset($data['load_list'])) {
            $loadList = $data['load_list'] ? true : false;
        }

        $chatsList = socketHelp()->chatsMessageList($uid, $loadList);

        if ($chatsList === false) {
            return $this->error();
        }

        return $this->success('获取成功!', ['list' => $chatsList]);
    }

    /**
     * 未读消息列表
     * @return bool
     * @throws \Exception
     */
    public function unReadMessageList()
    {

        $uid = $this->getUid();

        $loadList = true;

        if (isset($data['loadList'])) {
            $loadList = $data['loadList'] ? true : false;
        }

        $chatsList = socketHelp()->unReadMessageList($uid, $loadList);

        if ($chatsList === false) {
            return $this->error();
        }

        return $this->success('获取成功!', ['list' => $chatsList]);
    }

    /**
     * 更新签名
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function upSign()
    {

        $args = $this->getArgs();

        if (empty($args['sign'])) {
            return $this->error('参数错误!');
        }

        $uid = $this->getUid();

        if (\App\WebSocket\Model\Chats\ChatsUserModel::getInstance()->upSign($uid, $args['sign']) === false) {
            return $this->error();
        }

        return $this->success('修改成功!');
    }
}
