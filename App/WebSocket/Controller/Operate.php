<?php

namespace App\WebSocket\Controller;

use EasySwoole\Mysqli\QueryBuilder;

/**
 * operate(操作)类
 */
class Operate extends Base
{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 设置消息已读
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function setMessageRead()
    {

        $args = $this->getArgs();

        $uid = $this->getUid();

        $chatsId = isset($args['chats_id']) ? $args['chats_id'] : '';

        $unreadCount = !empty($data['unread_count']) ? $data['unread_count'] : 0;

        if (empty($chatsId)) {
            if (isset($data['uid']) && $data['uid'] != '') {
                $chatsId = socketHelp()->userChatsId($data['uid'], $uid);
            } else if (isset($data['group_id']) && $data['group_id'] != '') {
                $chatsId = socketHelp()->groupChatsId($data['group_id']);
            }
        }

        if (empty($chatsId)) {
            return $this->error('参数错误!');
        }

        $model = \App\WebSocket\Model\Chats\ChatsIdListModel::getInstance();

        if (empty($unreadCount)) {
            $model::create()->where(['uid' => $uid, 'chats_id' => $chatsId])->data(['unread_count' => 0])->update();
        } else {
            $info = $model::create()->where(['uid' => $uid, 'chats_id' => $chatsId])->field(['id', 'unread_count'])->get();
            if (empty($info)) {
                return $this->error('无效的消息!');
            }
            $info = $info->toArray();
            if ($info['unread_count'] < $unreadCount) {
                $unreadCount = $info['unread_count'];
            }

            //减少已读数量
            $model::create()->where(['id' => $info['id']])->update(['unread_count' => QueryBuilder::dec($unreadCount)]);
        }

        return $this->success('ok');
    }
}
