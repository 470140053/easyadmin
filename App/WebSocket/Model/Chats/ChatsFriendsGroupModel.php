<?php

/**
 * 会话好友列表分组
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;

class ChatsFriendsGroupModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'chats_friends_group';

    //主键字段
    public $primaryKey = 'group_id';

    /**
     * 获取分组列表
     * @param string $uid
     * @param int $limit
     * @return array|bool
     * @throws \Exception
     */
    public function getGroupList(string $uid,$limit = 0){

        if(!is_array($limit)){
            $limit = [$limit];
        }

        $list = self::create()->where(['uid' => $uid])->field([$this->primaryKey,'group_name'])->limit(...$limit)
            ->order('sort','asc','create_time','asc')->all();

        if(empty($list)){

            $data = $this->createDefault($uid);

            if($data === false)
                return false;

            $list = [];
            $list[] = [
                $this->primaryKey => $data[$this->primaryKey],
                'group_name'      => $data['group_name']
            ];
        }

        return $list;
    }


    /**
     * 创建默认分组数据
     * @param $uid
     * @return array|bool|false|mixed|string
     * @throws \Exception
     */
    public function createDefault($uid){

        return $this->createGroup($uid,'我的好友');
    }

    /**
     * 创建分组数据
     * @param string $uid 用户ID
     * @param string $groupName 群组名称
     * @return array|bool|false|mixed|string
     * @throws \Exception
     */
    public function createGroup(string $uid,string $groupName){

        $time = time();

        $data = [
            'uid'           => $uid,
            'group_name'    => $groupName,
            'sort'          => 0,
            'create_time'   => $time,
            'update_time'   => $time
        ];

        if(empty($data['uid'])){
            return $this->error('uid不能为空!');
        }

        if(empty($data['group_name'])){
            return $this->error('分组名称不能为空!');
        }

        $group_id = self::create()->data($data)->save();

        if(!$group_id){
            return $this->error('分组创建失败!');
        }

        $data[$this->primaryKey] = $group_id;

        return $data;
    }

    /**
     * 删除好友分组
     * @param string $uid 用户ID
     * @param string $groupId 好友分组ID
     * @param bool|null $handlingType 处理方式 null(不自动处理) true(移动好友后删除分组) false(删除好友后删除分组)
     * @return bool
     * @throws \Exception
     */
    public function delGroup(string $uid,string $groupId,?bool $handlingType = null){

        if($uid == '' || $groupId == ''){
            return $this->error('参数错误!');
        }

        if(!self::create()->where([$this->primaryKey => $groupId,'uid' => $uid])->count()){
            return $this->error('无效的分组!');
        }

        $model = ChatsFriendsModel::getInstance();

        //当前分组好友数量
        $friendsCount = $model::create()->where(['uid' => $uid,'group_id' => $groupId])->count();

        if(is_null($handlingType)){
            //不处理
            if($friendsCount){
                return $this->error('请先删除分组下的好友!');
            }

        }else if($handlingType){
            //移动好友
            if($model->groupMoveGroup($uid,$groupId) === false){
                return $this->error($model->getError());
            }

        }else{
            //删除好友
            $friendsList = $model::create()->where(['uid' => $uid,'group_id' => $groupId])->field(['uid','friend_uid'])->all();
            foreach ($friendsList as $friends){
                if($model->delFriends($friends['uid'],$friends['friend_uid']) === false){
                    return $this->error($model->getError());
                }
            }
        }

        //删除好友分组
        return $this->where([$this->primaryKey => $groupId])->destroy();
    }

    /**
     * 分组重命名
     * @param string $uid 用户ID
     * @param string $groupId 分组ID
     * @param string $groupName 重命名名称
     * @return bool
     */
    public function renameGroup(string $uid,string $groupId,string $groupName){

        if($uid == '' || $groupId == ''){
            return $this->error('参数错误!');
        }

        if($groupName == ''){
            return $this->error('请输入重命名名称!');
        }

        if(!self::create()->where([$this->primaryKey => $groupId,'uid' => $uid])->count()){
            return $this->error('无效的分组!');
        }

        return self::create()->where([$this->primaryKey => $groupId])->update(['group_name' => $groupName]);
    }

    /**
     * 分组排序
     * @param string $uid 用户ID
     * @param string $groupId 分组ID
     * @param int $sort 排序序列号 正序
     * @return bool
     */
    public function sortGroup(string $uid,string $groupId,int $sort = 0){

        if($uid == '' || $groupId == ''){
            return $this->error('参数错误!');
        }

        if($sort < 0){
            return $this->error('非法操作!');
        }

        if(!self::create()->where([$this->primaryKey => $groupId,'uid' => $uid])->count()){
            return $this->error('无效的分组!');
        }

        return self::create()->where([$this->primaryKey => $groupId])->update(['sort' => $sort]);
    }
    
}