<?php

/**
 * 会话好友列表
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;

class ChatsFriendsModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = 'tp_chats_friends';

    //主键字段
    public $primaryKey = 'friend_id';

    /**
     * 获取分组好友列表
     * @param string $uid
     * @return mixed
     * @throws \Exception
     */
    public function getGroupFriendsList(string $uid){

        $groupList = ChatsFriendsGroupModel::getInstance()->getGroupList($uid);

        $list = self::create()->where(['uid' => $uid])->field(['friend_uid','group_id','create_time'])->order('create_time')->all();

        if(!empty($list)){

            $friendUids = array_column($list,'friend_uid');

            $modelUser = ChatsUserModel::getInstance();

            $uidList = $modelUser::create()->where(['uid' => $friendUids])->field(['uid','nickname','avatar','sign','online'])->all();

            $list = from($list)->join($uidList,'$v["friend_uid"]','$v["uid"]',function ($v1,$v2) use($modelUser){
                $v2['avatar'] = $modelUser->getAvatar($v2['avatar']);
                return array_merge($v1,$v2);
            })->orderBy('$v["create_time"]')->groupBy('$v["group_id"]')->toArray();
        }

        foreach ($groupList as &$group){
            $group['list'] = isset($list[$group['group_id']]) ? $list[$group['group_id']] : [];
        }

        return $groupList;
    }


    /**
     * 添加好友
     * @param string $uid uid
     * @param string $friendUid 被添加人uid
     * @param string $groupId 聊天分组id
     * @return bool|mixed
     * @throws \Exception
     */
    public function addFriends(string $uid,string $friendUid,string $groupId = ''){

        if(empty($uid) || empty($friendUid)){
            return $this->error('参数错误!');
        }

        if($uid == $friendUid){
            return $this->error('不能加自己为好友!');
        }

        if(self::create()->where(['uid' => $uid,'friend_uid' => $friendUid])->count()){
            return $this->error('该用户已是您的好友!');
        }

        if(!ChatsUserModel::create()->where(['uid' => $friendUid])->count()){
            return $this->error('无效的操作用户!');
        }

        if(empty($groupId)){
            $groupList = ChatsFriendsGroupModel::getInstance()->getGroupList($uid,1);
            $groupId = isset($groupList[0]['group_id']) ? $groupList[0]['group_id'] : '';
        }else{
            if(!ChatsFriendsGroupModel::create()->where(['group_id' => $groupId,'uid' => $uid])->count()){
                return $this->error('无效的分组!');
            }
        }

        if(empty($groupId)){
            return $this->error('分组信息处理失败!');
        }

        $data = [
            'uid'           => $uid,
            'friend_uid'    => $friendUid,
            'group_id'      => $groupId,
            'create_time'   => time()
        ];

        if(empty($data['uid'])){
            return $this->error('uid不能为空!');
        }

        if(empty($data['friend_uid'])){
            return $this->error('friend_uid不能为空!');
        }

        if(empty($data['group_id'])){
            return $this->error('分组不能为空!');
        }

        return self::create()->data($data)->save();
    }


    /**
     * 删除好友
     * @param string $uid 用户ID
     * @param string $friendUid 被删除好友用户ID
     * @param bool $eachOther 是否双向删除
     * @return bool
     * @throws \Exception
     */
    public function delFriends(string $uid,string $friendUid,bool $eachOther = false){

        if(empty($uid) || empty($friendUid)){
            return $this->error('参数错误!');
        }

        if($uid == $friendUid){
            return $this->error('非法操作!');
        }

        if(!self::create()->where(['uid' => $uid,'friend_uid' => $friendUid])->count()){
            //非好友 跳过
            return $eachOther ? $this->delFriends($friendUid,$uid,false) : true;
        }

        $chatsId = socketHelp()->userChatsId($uid,$friendUid);

        //最后一次操作才触发自动删除
        $autoDel = $eachOther ? false : true;

        $modelIdList = ChatsIdListModel::getInstance();
        //删除会话列表数据
        if($modelIdList->delChatsMessage($uid,$chatsId,$autoDel) === false){
            return $this->error($modelIdList->getError());
        }

        if($eachOther){
            return $this->delFriends($friendUid,$uid,false);
        }

        return true;
    }


    /**
     * 好友移动分组
     * @param string $uid 用户ID
     * @param string|array $moveUidArr 需要移动的好友UID
     * @param string $moveGroupId 需要移动的分组ID
     * @return bool
     * @throws \Exception
     */
    public function friendsMoveGroup(string $uid,$moveUidArr,string $moveGroupId){

        is_array($moveUidArr) || $moveUidArr = explode(',',$moveUidArr);

        if($uid == '' || empty($moveUidArr) || $moveGroupId == ''){
            return $this->error('参数错误!');
        }

        $model = ChatsFriendsGroupModel::getInstance();

        if(!$model::create()->where(['group_id' => $moveGroupId,'uid' => $uid])->count()){
            return $this->error('无效的分组!');
        }

        return self::create()->where(['uid' => $uid,'friend_uid' => $moveUidArr])->update(['group_id' => $moveGroupId]);
    }

    /**
     * 好友分组移动到另一个分组
     * @param string $uid 用户ID
     * @param string $groupId 原分组ID
     * @param string $moveGroupId 需要移动的分组ID
     * @return bool
     * @throws \Exception
     */
    public function groupMoveGroup(string $uid,string $groupId,string $moveGroupId = ''){

        if($uid == '' || $groupId == ''){
            return $this->error('参数错误!');
        }

        $model = ChatsFriendsGroupModel::getInstance();

        if(empty($moveGroupId)){
            $groupList = $model->getGroupList($uid,1);
            $moveGroupId = isset($groupList[0]['group_id']) ? $groupList[0]['group_id'] : '';
        }

        if($groupId == $moveGroupId){
            return $this->error('被移动分组相同,操作失败!');
        }


        if(empty($moveGroupId) || !$model::create()->where(['group_id' => $moveGroupId,'uid' => $uid])->count()){
            return $this->error('无效被移动的分组!');
        }

        if(!$model::create()->where(['group_id' => $groupId,'uid' => $uid])->count()){
            return $this->error('无效的分组!');
        }

        return self::create()->where(['uid' => $uid,'group_id' => $groupId])->update(['group_id' => $moveGroupId]);
    }

}