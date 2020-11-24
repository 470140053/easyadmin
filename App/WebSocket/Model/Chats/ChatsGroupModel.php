<?php

/**
 * 会话群组表
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;

class ChatsGroupModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = 'tp_chats_group';

    //主键字段
    public $primaryKey = 'group_id';

    /**
     * 分组编号
     * @return int
     */
    public function groupNo(){

        $new = self::create()->field(['group_no'])->order('group_no','desc')->get();

        $new = !empty($new) ? $new->toArray() : [];

        $groupNo = null;

        if(!empty($new['group_no'])){
            $groupNo = $new['group_no'] + 1;
        }

        return $groupNo ?: 10000;
    }

    /**
     * 创建群聊
     * @param string $uid 管理用户ID
     * @param string $groupName 群ID
     * @return bool|mixed
     * @throws \Exception
     */
    public function createGroup(string $uid,string $groupName){

        if(empty($uid)){
            return $this->error('参数错误!');
        }

        if(empty($groupName)){
            return $this->error('请输入群名称!');
        }

        $userInfo = socketHelp()->userInfo($uid);

        if(empty($userInfo)){
            return $this->error('无效的用户!');
        }

        $time = time();

        $data = [
            'group_name'  => $groupName,
            'create_time' => $time,
            'update_time' => $time
        ];

        $data['group_no'] = $this->groupNo();

        if(empty($data['group_name'])){
            return $this->error('群组名称不能为空!');
        }

        $groupId = self::create()->data($data)->save();

        if(!$groupId){
            return $this->error('群聊创建失败!');
        }

        //添加群成员
        $memberId = ChatsGroupMembersModel::getInstance()->addGroupMembers($groupId,$uid,1);

        if($memberId === false){
            self::create()->where([$this->primaryKey => $groupId])->destroy();
            return $this->error(ChatsGroupMembersModel::getInstance()->getError());
        }

        return $groupId;
    }

    /**
     * 删除(解散)群聊
     * @param string $groupId 群聊ID
     * @param bool $autoClear 是否自动清理用户
     * @return bool
     * @throws \Exception
     */
    public function delGroup(string $groupId,bool $autoClear = false){

        if(empty($groupId)){
            return $this->error('参数错误!');
        }

        $model = ChatsGroupMembersModel::getInstance();

        if($autoClear && $model::create()->where(['group_id' => $groupId])->count()){
            //自动清理群成员
            if(!$model::create()->where(['group_id' => $groupId])->destroy()){
                return $this->error('解散群聊失败!');
            }
        }else if(!$autoClear && $model::create()->where(['group_id' => $groupId])->count()){
            return $this->error('请先删除群内成员!');
        }

        //删除群组
        if(!self::create()->where([$this->primaryKey => $groupId])->destroy()){
            return $this->error('删除群聊失败!');
        }

        $chatsId = socketHelp()->groupChatsId($groupId);

        //自动清理聊天记录
        ChatsLogModel::getInstance()->autoDelLog($chatsId);

        return true;
    }

    /**
     * 获取群组列表
     * @param string $uid
     * @return array
     * @throws \Exception
     */
    public function getGroupList(string $uid){

        $groupList = ChatsGroupMembersModel::create()->where(['uid' => $uid])->field(['group_id','create_time'])->order('create_time')->all();

        if(empty($groupList)){
            return [];
        }

        $groupIds = array_column($groupList,'group_id');

        $list = self::create()->where([$this->primaryKey => $groupIds])->all();

        $listDictionary = from(empty($list) ? [] : $list)->toDictionary('$v["group_id"]','$v');

        foreach ($groupList as &$group){

            $groupData = isset($listDictionary[$group['group_id']]) ? $listDictionary[$group['group_id']] : [];

            $groupData['join_time'] = $group['create_time'];

            $groupData['avatar'] = $this->getAvatar($groupData['avatar']);

            $group = $groupData;
        }

        unset($group);

        return $groupList;
    }

    /**
     * 获取群组信息
     * @param string $groupId
     * @return array|bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getGroupInfo(string $groupId){

        if($groupId == ''){
            return $this->error('参数错误!');
        }

        $fields = [
            '*'
        ];

        $info = self::create()->where([$this->primaryKey => $groupId])->field($fields)->get();

        $info = !empty($info) ? $info->toArray() : [];

        if(empty($info)){
            return $this->error('无效的群组!');
        }

        return $info;
    }

    /**
     * 获取群主信息
     * @param string $groupId
     * @return array
     * @throws \Exception
     */
    public function getGroupManager(string $groupId){

        $list = ChatsGroupMembersModel::getInstance()->getGroupMembers($groupId,['manage_level' => 1]);

        if(empty($list)){
            return [];
        }

        return $list[0];
    }

    /**
     * 获取头像
     * @param $avatar
     * @return string
     */
    public function getAvatar($avatar) : string {
        if(empty($avatar)) {
            return domain() . '/public/chats/images/group_avatar.png';
        }
        return '';// \App\Common\QiNiuUpload::StaticReturnAuthPath($avatar);;
    }

}