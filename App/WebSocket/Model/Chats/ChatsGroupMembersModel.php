<?php

/**
 * 会话群组表
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

class ChatsGroupMembersModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = 'tp_chats_group_members';

    //主键字段
    public $primaryKey = 'id';

    /**
     * 获取管理级别列表
     * @return array
     */
    public function getManageLevelList(){
        return [
            0       => [
                'text'      => '普通成员',
            ],
            1       => [
                'num'       => 1,//最大数量
                'text'      => '群主',
                'sort'      => 1,//排序权重
            ],
            2       => [
                'num'       => 5,
                'text'      => '管理员',
                'sort'      => 10
            ]
        ];
    }

    /**
     * 获取可管理级别
     * @param int $manageLevel
     * @return array|mixed
     */
    public function getManageLevel(int $manageLevel){

        $arr = [
            1       => '*',
            2       => [
                0
            ],
            0       => []
        ];

        return isset($arr[$manageLevel]) ? $arr[$manageLevel] : [];
    }

    /**
     * 是否是群主
     * @param int $manageLevel
     * @return bool
     */
    public function isGroupManager(int $manageLevel) : bool {

        return $manageLevel === 1;
    }

    /**
     * 是否有权利权限
     * @param int $manageLevel 管理级别
     * @param int $manageLevel2 被管理级别
     * @return bool
     */
    public function isManageRight(int $manageLevel,int $manageLevel2) : bool {

        $levelArr = $this->getManageLevel($manageLevel);

        if($levelArr == '*'){
            return true;
        }

        return in_array($manageLevel2,$levelArr);
    }

    /**
     * 获取管理级别名称
     * @param int $manageLevel
     * @return string
     */
    public function getManageText(int $manageLevel) : string {

        $manageLevelList = $this->getManageLevelList();

        return isset($manageLevelList[$manageLevel]['text']) ? $manageLevelList[$manageLevel]['text'] : '';
    }

    /**
     * 获取排序级别
     * @param int $manageLevel
     * @return int
     */
    public function getSort(int $manageLevel) : int {

        $sort = 999;

        $manageLevelList = $this->getManageLevelList();

        if(isset($manageLevelList[$manageLevel]['sort'])){
            $sort = $manageLevelList[$manageLevel]['sort'];
        }

        return $sort;
    }

    /**
     * 获取最大数量
     * @param int $manageLevel
     * @return int|null
     */
    public function getMaxNum(int $manageLevel) : ?int {

        $manageLevelList = $this->getManageLevelList();

        return isset($manageLevelList[$manageLevel]['num']) ? $manageLevelList[$manageLevel]['num'] : null;
    }


    /**
     * 添加群组成员
     * @param string $groupId 群组ID
     * @param string $uid uid
     * @param int $manageLevel 管理级别
     * @return bool|mixed
     * @throws \Exception
     */
    public function addGroupMembers(string $groupId,string $uid,int $manageLevel = 0){

        if($groupId == '' || $uid == ''){
            return $this->error('参数错误!');
        }

        $userInfo = socketHelp()->userInfo($uid);

        if(empty($userInfo)){
            return $this->error('无效的用户!');
        }

        if(!isset($this->getManageLevelList()[$manageLevel])){
            return $this->error('无效的用户级别!');
        }

        if(!ChatsGroupModel::create()->where(['group_id' => $groupId])->count()){
            return $this->error('无效的群组!');
        }

        if(self::create()->where(['group_id' => $groupId,'uid' => $uid])->count()){
            return $this->error('你已是该群组成员!');
        }

        $maxNum = $this->getMaxNum($manageLevel);

        if(!is_null($maxNum) && self::create()->where(['group_id' => $groupId,'manage_level' => $manageLevel])->count() >= $maxNum){
            return $this->error($this->getManageText($manageLevel) . "最多设置{$maxNum}位");
        }

        $time = time();

        $data = [
            'group_id'          => $groupId,
            'uid'               => $uid,
            'manage_level'      => $manageLevel,
            'sort'              => $this->getSort($manageLevel),
            'create_time'       => $time,
            'update_time'       => $time
        ];

        if(empty($data['group_id'])){
            return $this->error('群ID不能为空!');
        }

        if(empty($data['uid'])){
            return $this->error('uid不能为空!');
        }

        $id = self::create()->data($data)->save();

        if(!$id){
            return $this->error('加入群聊失败!');
        }

        //递增数据
        ChatsGroupModel::create()->where(['group_id' => $groupId])->update(['user_count' => QueryBuilder::inc(1)]);

        //删除群组缓存
        socketHelp()->delGroupMembersList($groupId);

        return $id;
    }

    /**
     * 移除群用户
     * @param string $groupId 群聊ID
     * @param string $uid 用户ID
     * @param string $removeUid 被移除用户ID
     * @return bool
     * @throws \Exception
     */
    public function removeGroupMembers(string $groupId,string $uid,string $removeUid){

        if(empty($groupId) || empty($uid) || empty($removeUid)){
            return $this->error('参数错误!');
        }

        $info = self::create()->where(['group_id' => $groupId,'uid' => $uid])->field(['uid','manage_level'])->get();

        $info = !empty($info) ? $info->toArray() : [];

        if(empty($info)){
            return $this->error('你不是此群成员!');
        }

        $removeInfo = self::create()->where(['group_id' => $groupId,'uid' => $removeUid])->field([$this->primaryKey,'uid','manage_level'])->get();

        $removeInfo = !empty($removeInfo) ? $removeInfo->toArray() : [];

        if(empty($removeInfo)){
            return $this->error('无效的移除用户!');
        }

        $manageLevel = (int)$info['manage_level'];
        $manageLevel2 = (int)$removeInfo['manage_level'];

        //判断是否有权限操作
        if(!$this->isManageRight($manageLevel,$manageLevel2)){
            return $this->error('你没有权限操作该用户!');
        }

        //移除用户
        self::create()->where([$this->primaryKey => $removeInfo[$this->primaryKey]])->destroy();

        //递减数据
        ChatsGroupModel::create()->where(['group_id' => $groupId])->update(['user_count' => QueryBuilder::dec(1)]);

        $chatsId = socketHelp()->groupChatsId($groupId);

        //删除会话消息
        ChatsIdListModel::getInstance()->delChatsMessage($removeUid,$chatsId);

        //删除群组缓存
        socketHelp()->delGroupMembersList($groupId);
        return true;
    }


    /**
     * 退出群聊
     * @param string $groupId
     * @param string $uid
     * @return bool
     * @throws \Exception
     */
    public function exitGroupMembers(string $groupId,string $uid){

        if(empty($groupId) || empty($uid)){
            return $this->error('参数错误!');
        }

        $info = self::create()->where(['group_id' => $groupId,'uid' => $uid])->field([$this->primaryKey,'uid','manage_level'])->get();

        $info = !empty($info) ? $info->toArray() : [];

        if(empty($info)){
            return $this->error('你不是此群成员!');
        }

        $manageLevel = (int)$info['manage_level'];

        //是否解散群聊
        $isDissolve = false;

        if($this->isGroupManager($manageLevel)){

            if(self::create()->where(['group_id' => $groupId,'uid[!]' => $uid])->count()){
                return $this->error('请先移除群里其他成员!');
            }else{
                $isDissolve = true;
            }
        }

        //退出群聊
        self::create()->where([$this->primaryKey => $info[$this->primaryKey]])->destroy();

        //递减数据
        ChatsGroupModel::create()->where(['group_id' => $groupId])->update(['user_count' => QueryBuilder::dec(1)]);

        $chatsId = socketHelp()->groupChatsId($groupId);

        //删除会话消息
        ChatsIdListModel::getInstance()->delChatsMessage($uid,$chatsId);

        if($isDissolve){
            ChatsGroupModel::getInstance()->delGroup($groupId);
        }

        //删除群组缓存
        socketHelp()->delGroupMembersList($groupId);
        return true;
    }

    /**
     * 获取群组成员列表
     * @param string $groupId
     * @return array|bool
     * @throws \Exception
     */
    public function getGroupMembersList(string $groupId){

        return $this->getGroupMembers($groupId);
    }

    /**
     * 获取群组成员
     * @param string $groupId
     * @param array $where
     * @return array|bool
     * @throws \Exception
     */
    public function getGroupMembers(string $groupId,array $where = []){

        if($groupId == ''){
            return $this->error('参数错误');
        }

        $fields = [
            'group_id',
            'uid',
            'manage_level',
            'sort',
            'create_time'
        ];

        $where['group_id'] = $groupId;

        $memberList = self::create()->where($where)->field($fields)->order('sort','asc','create_time','asc')->all();

        if(empty($memberList)){
            return [];
        }

        $uids = array_column($memberList,'uid');

        $userList = ChatsUserModel::getInstance()->getUserList(['uid' => $uids]);

        $userDic = from(empty($userList) ? [] : $userList)->toDictionary('$v["uid"]','$v');

        $list = [];

        foreach ($memberList as $member){
            if(!isset($userDic[$member['uid']])){
                continue;
            }

            $list[] = array_merge($member,$userDic[$member['uid']]);
        }

        return $list;
    }

}