<?php

/**
 * 会话ID list表(最近聊天记录)
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

class ChatsIdListModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = 'tp_chats_id_list';

    //主键字段
    public $primaryKey = 'id';

    /**
     * 添加会话ID
     * @param string $uid 说话人
     * @param string $to 被说话人
     * @param string $chatsId 会话key
     * @param bool $isNext 是否是第二次(接收方)
     * @return bool
     * @throws \Exception
     */
    public function addChatsList(string $uid,string $to,string $chatsId,$isNext = false) {

        if(empty($uid) || empty($to) || empty($chatsId))
            return $this->error('参数错误!');

        $idListData = self::create()->where(['uid' => $uid,'chats_id' => $chatsId])->field([$this->primaryKey])->get();

        $idListData = !empty($idListData) ? $idListData->toArray() : [];

        $info = socketHelp()->userInfo($to);

        if(!empty($idListData)){

            //更新记录
            self::create()->where([$this->primaryKey => $idListData[$this->primaryKey]])->update([
                'nickname'      => $info['nickname'],
                'avatar'        => $info['avatar'],
                'update_time'   => time()
            ]);

            //增加未读数量
            if($isNext){
                self::create()->where([$this->primaryKey => $idListData[$this->primaryKey]])->update(['unread_count' => QueryBuilder::inc(1)]);
            }

            return $isNext ?: $this->addChatsList($to,$uid,$chatsId,true);
        }

        $time = time();

        //添加
        $data = [
            'uid'           => $uid,
            'chats_id'      => $chatsId,
            'nickname'      => $info['nickname'],
            'avatar'        => $info['avatar'],
            'unread_count'  => $isNext ? 1 : 0,
            'update_time'   => $time,
            'create_time'   => $time
        ];

        if(empty($data['uid'])){
            return $this->error('uid不能为空!');
        }

        if(empty($data['chats_id'])){
            return $this->error('会话ID不能为空!');
        }

        if(self::create()->data($data)->save()){
            return $this->error('会话添加失败!');
        }

        return $isNext ?: $this->addChatsList($to,$uid,$chatsId,true);
    }


    /**
     * 获取会话列表
     * @param string $uid
     * @param bool $loadList
     * @return array|bool
     * @throws \Exception
     */
    public function chatsMessageList(string $uid,bool $loadList = false){

        if(empty($uid))
            return $this->error('参数错误!');

        $limit = 1000;

        $list = self::create()->where(['uid' => $uid])
            ->field(['uid','chats_id','nickname','avatar','unread_count','update_time'])
            ->order('update_time','desc')->limit($limit)->all();

        return $this->buildChatsList($list,$loadList);
    }


    /**
     * 获取未读消息列表
     * @param string $uid
     * @param bool $loadList
     * @return array|bool
     * @throws \Exception
     */
    public function unReadMessageList(string $uid,bool $loadList = true){

        if(empty($uid))
            return $this->error('参数错误!');

        $list = self::create()->where(['uid' => $uid,'unread_count' => [0,'>']])
            ->field(['uid','chats_id','nickname','avatar','unread_count','update_time','mark','over'])
            ->order('update_time','desc')->all();

        return $this->buildChatsList($list,$loadList);
    }

    /**
     * 组装chats list
     * @param array $list
     * @param bool $loadList 是否加载list
     * @param bool|null $ucFirst
     * @return array
     * @throws \Exception
     */
    public function buildChatsList(array $list,bool $loadList = false,?bool $ucFirst = false) : array {

        if(empty($list))
            return [];

        $chatsIds = array_column($list,'chats_id');

        $chatsList = [];

        $target = ChatsIdModel::getInstance();

        if(!empty($chatsIds)){
            $chatsList = $target::create()->where(['key' => $chatsIds])->field(['key','text','description','type'])->all();
            $chatsList = from(empty($chatsList) ? [] : $chatsList)->toDictionary('$v["key"]','$v');
        }


        $targetUser = ChatsUserModel::getInstance();

        $targetGroup = ChatsGroupModel::getInstance();

        $targetLog = ChatsLogModel::getInstance();

        foreach ($list as &$idList){

            $chatsData = $chatsList[$idList['chats_id']] ? $chatsList[$idList['chats_id']] : [];

            $idList['description'] = '';
            $idList['toId'] = '';
            $idList['type'] = '';

            if(!empty($chatsData)){
                $idList['description'] = $chatsData['description'];
                $idList['toId'] = $target->parsingText($idList['uid'],$chatsData['text'],$chatsData['type']);
                $idList['type'] = $chatsData['type'];
            }

            switch ($idList['type']){
                case 'user':
                    //获取在线状态
                    $onlineStatus = socketHelp()->uidOnlineStatus($idList['toId']);
                    $idList['avatar'] = $targetUser->getAvatar($idList['avatar']);
                    $idList['online'] = $onlineStatus;
                    break;

                case 'group':
                    $idList['avatar'] = $targetGroup->getAvatar($idList['avatar']);
                    break;
            }

            if($loadList){
                if($idList['unread_count']){
                    $logList = $targetLog->getLogList(['chats_id' => $idList['chats_id']],$idList['unread_count']);
                    $idList['list'] = $logList === false ? [] : $logList;
                }else{
                    $idList['list'] = [];
                }
            }

            unset($idList['uid']);

            //数组下斜线转小驼峰
            if(!is_null($ucFirst)){
                $idList = arrayConvertUnderline($idList,$ucFirst);
            }
        }

        unset($idList);

        return $list;
    }


    /**
     * 删除会话消息
     * @param string $uid 用户ID
     * @param string $chatsId 会话ID
     * @param bool $autoDel 是否自动清理聊天记录
     * @return bool
     * @throws \Exception
     */
    public function delChatsMessage(string $uid,string $chatsId,bool $autoDel = false){

        if($uid == '' || $chatsId == ''){
            return $this->error('参数错误!');
        }

        if(!self::create()->where(['uid' => $uid,'chats_id' => $chatsId])->destroy()){
            return $this->error('会话消息删除失败!');
        }

        if($autoDel){
            //自动清理聊天记录
            ChatsLogModel::getInstance()->autoDelLog($chatsId);
        }

        return true;
    }

}