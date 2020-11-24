<?php

/**
 * 会话ID
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

class ChatsIdModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = 'tp_chats_id';

    //主键字段
    public $primaryKey = 'id';

    /**
     * 添加会话ID
     * @param string $key
     * @param string $text
     * @param string $type
     * @return bool|mixed
     * @throws \Exception
     */
    public function addChatsId(string $key,string $text,string $type) {

        if(empty($key) || empty($text) || empty($type))
            return $this->error('参数错误!');

        $time = time();

        $data = [
            'key'           => $key,
            'text'          => $text,
            'type'          => $type,
            'update_time'   => $time,
            'create_time'   => $time
        ];

        if($this->where(['key' => $key])->count()){
            return $this->error('已存在相同的key');
        }

        return self::create()->data($data)->save();
    }

    /**
     * user(单聊) type 需要添加的数据
     * @param string $uid
     * @param string $to
     * @param string $chatsId
     * @return bool
     * @throws \Exception
     */
    public function typeUser(string $uid,string $to,string $chatsId){

        $model = ChatsIdListModel::getInstance();

        if($model->addChatsList($uid,$to,$chatsId) === false){
            return $this->error($model->getError());
        }

        return true;
    }

    /**
     * 系统(单聊)基础方法 type 需要添加的数据
     * @param string $uid
     * @param string $to
     * @param string $chatsId
     * @return bool
     * @throws \Exception
     */
    public function typeSystem(string $uid,string $to,string $chatsId){

        $model = ChatsIdListModel::getInstance();
        //只需要添加接收方
        if($model->addChatsList($to,$uid,$chatsId,true) === false){
            return $this->error($model->getError());
        }

        return true;
    }

    /**
     * group(群组) type 需要添加的数据
     * @param string $uid
     * @param string $to
     * @param string $chatsId
     * @return bool
     * @throws \Exception
     */
    public function typeGroup(string $uid,string $to,string $chatsId){

        $membersList = socketHelp()->groupMembersList($to);

        if(empty($membersList)){
            return $this->error('没有群成员数据!');
        }

        $uidList = array_column($membersList,'uid');

        $uidList = array_unique($uidList);

        $groupInfo = ChatsGroupModel::getInstance()->getGroupInfo($to);

        //查询需要添加的
        $list = ChatsIdListModel::create()->where(['uid' => $uidList,'chats_id' => $chatsId])->field(['id','uid'])->all();

        $idDic = from(empty($list) ? [] : $list)->toDictionary('$v["uid"]','$v["id"]');

        $time = time();

        //需要添加的值
        $addData = [];

        //需要更新的id
        $upIds = [];

        $upData = [
            'update_time'       => $time,
            'nickname'          => $groupInfo['group_name'],
            'avatar'            => $groupInfo['avatar']
        ];

        $uidIndex = array_search($uid,$uidList);

        if($uidIndex !== false){
            //移除元素
            array_splice($uidList,$uidIndex,1);
        }

        if(!isset($idDic[$uid])){
            $addData[] = [
                'uid'           => $uid,
                'chats_id'      => $chatsId,
                'nickname'      => $groupInfo['group_name'],
                'avatar'        => $groupInfo['avatar'],
                'unread_count'  => 0,
                'create_time'   => $time,
                'update_time'   => $time
            ];
        }

        foreach ($uidList as $id){

            if(isset($idDic[$id])){
                $upIds[] = $idDic[$id];
            }else{
                $addData[] = [
                    'uid'           => $id,
                    'chats_id'      => $chatsId,
                    'nickname'      => $groupInfo['group_name'],
                    'avatar'        => $groupInfo['avatar'],
                    'unread_count'  => 1,
                    'create_time'   => $time,
                    'update_time'   => $time
                ];
            }
        }

        if(!empty($addData)){
            ChatsIdListModel::create()->saveAll($addData);
        }

        if(!empty($upIds)){
            ChatsIdListModel::create()->where(['id' => $upIds])->update($upData);
            ChatsIdListModel::create()->where(['id' => $upIds])->update(['unread_count' => QueryBuilder::inc(1)]);
        }

        return true;
    }

    /**
     * 房间 type 需要添加的数据
     * @param string $uid
     * @param string $to
     * @param string $chatsId
     * @return bool
     * @throws \Exception
     */
    public function typeRoom(string $uid,string $to,string $chatsId){

        $mHelp = socketHelp()->roomHelp($to);
        $membersList = $mHelp->getRoomUserAll();

        if(empty($membersList)){
            return $this->error('没有群成员数据!');
        }

        $uidList = array_column($membersList,'uid');

        $uidList = array_unique($uidList);

        $roomInfo = $mHelp->getRoomData();

        //查询需要添加的
        $list = ChatsIdListModel::create()->where(['uid' => $uidList,'chats_id' => $chatsId])->field(['id','uid'])->all();

        $idDic = from(empty($list) ? [] : $list)->toDictionary('$v["uid"]','$v["id"]');

        $time = time();

        //需要添加的值
        $addData = [];

        //需要更新的id
        $upIds = [];

        $upData = [
            'update_time'       => $time,
            'nickname'          => $roomInfo['room_name'],
            'avatar'            => $roomInfo['room_logo']
        ];

        $uidIndex = array_search($uid,$uidList);

        if($uidIndex !== false){
            //移除元素
            array_splice($uidList,$uidIndex,1);
        }

        if(!isset($idDic[$uid])){
            $addData[] = [
                'uid'          => $uid,
                'chats_id'     => $chatsId,
                'nickname'     => $roomInfo['room_name'],
                'avatar'       => $roomInfo['room_logo'],
                'unread_count' => 0,
                'create_time'  => $time,
                'update_time'  => $time
            ];
        }

        foreach ($uidList as $id){

            if(isset($idDic[$id])){
                $upIds[] = $idDic[$id];
            }else{
                $addData[] = [
                    'uid'           => $id,
                    'chats_id'      => $chatsId,
                    'nickname'     => $roomInfo['room_name'],
                    'avatar'       => $roomInfo['room_logo'],
                    'unread_count'  => 1,
                    'create_time'   => $time,
                    'update_time'   => $time
                ];
            }
        }

        if(!empty($addData)){
            ChatsIdListModel::create()->saveAll($addData);
        }

        if(!empty($upIds)){
            ChatsIdListModel::create()->where(['id' => $upIds])->update($upData);
            ChatsIdListModel::create()->where(['id' => $upIds])->update(['unread_count' => QueryBuilder::inc(1)]);
        }

        return true;
    }

    /**
     * 解析text
     * @param string $str
     * @param string $text 需要解析的文本
     * @param string $type 类型
     * @return string|array
     */
    public function parsingText(string $str,string $text,string $type) {

        $emptyStr = '';

        switch ($type){
            case 'user':
                $arr = $this->getTextArray($text,$type);
                foreach ($arr as $val){
                    if($val !== $str){
                        return $val;
                    }
                }
                break;

            case 'group':
                $arr = $this->getTextArray($text,$type);
                return reset($arr);
                break;

            case 'system':
                $arr = $this->getTextArray($text,$type);
                foreach ($arr as $val){
                    if($val !== $str){
                        return $val;
                    }
                }
                break;
        }

        return $emptyStr;
    }

    /**
     * 获取text数组
     * @param string $text
     * @param string $type
     * @return array
     */
    public function getTextArray(string $text,string $type){

        $arr = [];

        switch ($type){
            case 'user':
                $arr = explode('|',$text);
                break;

            case 'group':
                $arr = explode('|',$text);
                $groupIndex = array_search('group',$arr);
                if($groupIndex !== false){
                    array_splice($arr,$groupIndex,1);
                }
                break;

            case 'room':
                $arr = explode('|',$text);
                $groupIndex = array_search('room',$arr);
                if($groupIndex !== false){
                    array_splice($arr,$groupIndex,1);
                }
                break;

            case 'system':
                $arr = explode('|',$text);
                foreach ($arr as $key => $vo){
                    if(strpos($type,'system@') === 0){
                        array_splice($arr,$key,1);
                        break;
                    }
                }
                break;
        }

        return $arr;
    }

}