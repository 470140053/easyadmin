<?php
namespace App\Common;

use App\WebSocket\Model\Chats\ChatsFriendsGroupModel;
use App\WebSocket\Model\Chats\ChatsFriendsModel;
use App\WebSocket\Model\Chats\ChatsGroupMembersModel;
use App\WebSocket\Model\Chats\ChatsGroupModel;
use App\WebSocket\Model\Chats\ChatsIdListModel;
use App\WebSocket\Model\Chats\ChatsLogModel;

/**
 * 队列
 */
class SocketHelp{

    use \EasySwoole\Component\Singleton;

    /**
     * redis
     * @return \EasySwoole\Redis\Redis|null
     */
    private function redis(){
        return eRedis();
    }

    /**
     * swoole服务
     * @return \Swoole\Http\Server|\Swoole\Server|\Swoole\Server\Port|\Swoole\WebSocket\Server|null
     */
    public function swooleServer(){
        return \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();
    }

    /**
     * 用户帮助文件
     * @return Socket\User
     */
    public function userHelp() : \App\Common\Socket\User {
        return \App\Common\Socket\User::getInstance();
    }

    /**
     * 房间帮助文件
     * @param $roomId
     * @return Socket\Room
     */
    public function roomHelp($roomId) : \App\Common\Socket\Room {
        return \App\Common\Socket\Room::getInstance($roomId);
    }

    /**
     * 通过fd 获取客户端信息
     * @param $fd
     * @return array
     */
    public function getClientInfo($fd) : array {
        $info = swooleServer()->getClientInfo($fd);
        return !empty($info) ? $info : [];
    }

    /**
     * 获取uid
     * @param $fd
     * @return int
     */
    public function getUid($fd) : int {
        $info = $this->getClientInfo($fd);
        return isset($info['uid']) ? $info['uid'] : 0;
    }

    /**
     * 获取用户信息
     * @param int $uid
     * @param bool $load
     * @return array
     */
    public function userInfo(int $uid,bool $load = false) : array {
        return $this->userHelp()->userInfo($uid,$load);
    }

    /**
     * 基础key名
     * @return string
     */
    private function baseBindKey(){
        return 'socket_fd:uid_';
    }

    /**
     * 绑定缓存key
     * @param $uid
     * @return string
     */
    public function bindKey($uid){
        return $this->baseBindKey() . $uid;
    }

    /**
     * 绑定fd
     * @param int $fd
     * @param int $uid
     * @return bool
     */
    public function bindFd(int $fd,int $uid){
        $key = $this->bindKey($uid);
        return $this->redis()->sAdd($key,$fd);
    }

    /**
     * 自动删除绑定
     * @param int $fd
     * @param array $info
     * @return bool
     */
    public function autoDelBindFd(int $fd,$info = []){
        $uid = $this->getUid($fd);
        $key = $this->bindKey($uid);
        $this->delBindFd($fd);

        $redis = $this->redis();

        $len = $redis->sCard($key);
        if(empty($len)){
            //删除用户数据
            $this->userHelp()->delUserInfo($uid);
        }

        return true;
    }

    /**
     * 删除fd 绑定
     * @param $fd
     * @return bool|string
     */
    public function delBindFd(int $fd){
        $uid = $this->getUid($fd);
        $key = $this->bindKey($uid);
        //钩子
        hook('socket','hookDelBindFd',[$fd]);
        //解除fd关联信息
        \App\Storage\FdConnect::getInstance()->delete($fd);
        return $this->redis()->sRem($key,$fd);
    }

    /**
     * 删除绑定的uid
     * @param $uid
     * @return bool|string
     */
    public function delBindUid($uid){
        $key = $this->bindKey($uid);
        return $this->redis()->del($key);
    }

    /**
     * uid获取所有fd
     * @param $uid
     * @return array|bool|string
     */
    public function uidGetFds($uid){
        $key = $this->bindKey($uid);
        return $this->redis()->sMembers($key);
    }

    /**
     * uid在线状态
     * @param $uid
     * @return bool|string
     */
    public function uidOnlineStatus($uid){
        $key = $this->bindKey($uid);
        return $this->redis()->exists($key);
    }

    /**
     * 删除所有在线数据
     * @return bool
     */
    public function unAllData(){

        $redis = $this->redis();

        //钩子
        hook('socket','hookUnAllData');

        $list = $redis->keys($this->baseBindKey() . '*');

        if(!is_array($list)){
            return false;
        }

        //删除key
        $redis->del(...array_values($list));
        return true;
    }

    /**
     * 时间戳 - 精确到毫秒
     * @return float
     */
    public function getMillisecond() {
        [$t1, $t2] = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }

    /**
     * socket fds 处理
     * @param array|string $fds
     * @param bool $isGet 是否获取 否则 set
     * @return array|string
     */
    public function socketFds($fds, bool $isGet = true) {

        if ($isGet && !is_array($fds)) {
            $fds = array_filter(explode(',', $fds));
        } else if (!$isGet && is_array($fds)) {
            $fds = implode(',', array_filter($fds));
        }

        return $fds;
    }

    /**
     * 推送uid
     * @param array|int $uids
     * @param array|string $data
     * @param null $server
     * @return bool
     */
    public function pushUid($uids,$data,$server = null) {

        if(!is_array($uids)){
            $uids = explode(',',$uids);
        }

        $fds = [];

        foreach ($uids as $uid){
            //通过uid获取fd
            $uFds = $this->uidGetFds($uid);

            if(empty($uFds)){
                continue;
            }

            array_splice($fds,count($fds),0,$uFds);
        }

        if(is_null($server)){
            $server = $this->swooleServer();
        }

        return $this->push($fds,$data,$server);
    }

    /**
     * 推送
     * @param array|int $fds
     * @param array|string $data
     * @param null $server
     * @return bool
     */
    public function push($fds,$data,$server = null){

        if(!is_array($fds)){
            $fds = explode(',',$fds);
        }

        if(empty($fds)){
            return false;
        }

        if(is_null($server)){
            $server = $this->swooleServer();
        }

        if(is_array($data)){
            $data = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        foreach ($fds as $fd){

            switch (true){
                case $server->isEstablished($fd)://webSocket
                    $server->push($fd,$data);
                    break;

                case $server->exist($fd)://tcp
                    $server->send($fd,$data);
                    break;

                default:
                    break;
            }
        }

        return true;
    }

    /**
     * fd关联 模块
     * @param $fd
     * @param array $arr
     * @return bool
     */
    public function fdConnect($fd,array $arr){
        return \App\Storage\FdConnect::getInstance()->connect($fd,$arr);
    }

    /**
     * 删除fd关联
     * @param $fd
     * @param string $arrKey
     * @return bool
     */
    public function delFdConnect($fd,string $arrKey){
        return \App\Storage\FdConnect::getInstance()->delConnect($fd,$arrKey);
    }

    /**
     * fd获取所在房间ID
     * @param $fd
     * @return int|mixed
     */
    public function fdGetRoomId($fd){
        $connectData = \App\Storage\FdConnect::getInstance()->getConnect($fd);
        return empty($connectData['room']) ? 0 : $connectData['room'];
    }

    /**
     * 会话ID
     * @param string $str1
     * @param string $str2
     * @param bool $isAdd
     * @param string $type 会话类型
     * @param string $addStr 追加数据
     * @return string
     * @throws \Exception
     */
    public function chatsId(string $str1, string $str2, bool $isAdd = false, string $type = '',string $addStr = ''): string {

        $str = null;
        //需要左边大
        if (strcmp($str1, $str2) < 0) {
            $str = "{$str1}|{$str2}";
        } else {
            $str = "{$str2}|{$str1}";
        }

        if(!is_null($str)){
            $str .= ($addStr!='' ? "|{$addStr}" : '');
        }

        $md5Str = md5($str);

        if ($isAdd) {
            //添加会话ID
            \App\WebSocket\Model\Chats\ChatsIdModel::getInstance()->addChatsId($md5Str, $str, $type);
        }

        return $md5Str;
    }

    /**
     * 单聊会话ID
     * @param string $str1
     * @param string $str2
     * @param bool $isAdd
     * @return string
     * @throws \Exception
     */
    public function userChatsId(string $str1, string $str2, bool $isAdd = false) {

        return $this->chatsId($str1, $str2, $isAdd, 'user');
    }

    /**
     * 群组会话ID
     * @param string $groupId
     * @param bool $isAdd
     * @return string
     * @throws \Exception
     */
    public function groupChatsId(string $groupId, bool $isAdd = false) {

        return $this->chatsId('group', $groupId, $isAdd, 'group');
    }

    /**
     * 房间会话ID
     * @param string $roomId
     * @param bool $isAdd
     * @return string
     * @throws \Exception
     */
    public function roomChatsId(string $roomId, bool $isAdd = false) {

        return $this->chatsId('room', $roomId, $isAdd, 'room');
    }

    /**
     * 系统会话ID
     * @param string $str1
     * @param string $str2
     * @param string $type 系统类型
     * @param bool $isAdd
     * @return string
     * @throws \Exception
     */
    public function systemChatsId(string $str1, string $str2,string $type, bool $isAdd = false) {

        return $this->chatsId($str1,$str2, $isAdd, 'system','system@'. $type);
    }

    /**
     * token转data
     * @param string $token
     * @return array|bool
     */
    public function tokenData(string $token) {

        $model = \App\WebSocket\Model\Chats\ChatsUserModel::getInstance();

        $returnData = $model->tokenData($token);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }


    /**
     * 验证token
     * @param string $token
     * @param bool $isLogin 是否登录验证
     * @return array|bool
     * @throws \Exception
     */
    public function verifyToken(string $token, bool $isLogin = false) {

        $model = \App\WebSocket\Model\Chats\ChatsUserModel::getInstance();

        $returnData = $model->verifyToken($token, $isLogin);

        if ($returnData === false){
            return returnUtil()->error($model->getError(), 401);
        }

        return $returnData;
    }

    /**
     * 获取token
     * @param string $has
     * @param int $hasId
     * @return bool|string
     * @throws \Exception
     */
    public function getToken(string $has, int $hasId) {

        $model = \App\WebSocket\Model\Chats\ChatsUserModel::getInstance();

        $returnData = $model->getToken($has, $hasId);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 群组成员缓存信息
     * @param string $groupId
     * @param array $list 数据
     * @param int|null $expire 缓存时间
     * @param bool $isAuto 获取时没有 是否自动添加
     * @return array
     * @throws \Exception
     */
    public function groupMembersList(string $groupId, array $list = [], ?int $expire = null, $isAuto = true): array {

//        $cache = self::cache();
//
//        $key = "chats_group_members_list:{$groupId}";
//
//        is_null($expire) && $expire = 2 * 24 * 60 * 60;
//
//        if (!empty($list)) {
//
//            $value = json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
//
//            //hMset
//            //设置用户信息缓存
//            $cache->set($key, $value, $expire);
//        } else {
//            $list = $cache->get($key);
//
//
//            if (!empty($list)) {
//                $list = json_decode($list, true);
//            } else if ($isAuto && empty($list)) {
//                //查询数据
//                $list = target('chats/ChatsGroupMembers')->getGroupMembersList($groupId);
//
//                !empty($list) && self::groupMembersList($groupId, $list, $expire);
//            }
//        }

        return $list ?: [];
    }

    /**
     * 删除群组信息
     * @param string $groupId
     * @return bool
     */
    public function delGroupMembersList(string $groupId): bool {

//        $cache = self::cache();
//
//        $key = "chats_group_members_list:{$groupId}";

//        return $cache->del($key);
        return true;
    }

    /**
     * 添加聊天记录
     * @param string $uid 说话人
     * @param string $to 被说话人
     * @param string $content 说话内容
     * @param string $type 说话类型
     * @param int|null $time 发送时间
     * @param array $params 额外数据
     * @return bool|mixed
     * @throws \Exception
     */
    public function addChatsLog(string $uid, string $to, string $content, string $type, ?int $time = null, array $params = []) {

        $model = ChatsLogModel::getInstance();

        $id = $model->addLog($uid, $to, $content, $type, $time, $params);

        if ($id === false){
            return returnUtil()->error($model->getError());
        }

        return $id;
    }

    /**
     * 添加用户聊天记录
     * @param string $uid 说话人
     * @param string $to 被说话人
     * @param string $content 说话内容
     * @param int|null $time 发送时间
     * @param array $params 额外数据
     * @return bool|mixed
     * @throws \Exception
     */
    public function addChatsUserLog(string $uid, string $to, string $content, ?int $time = null, array $params = []) {

        return $this->addChatsLog($uid, $to, $content, 'user', $time, $params);
    }

    /**
     * 添加群组聊天记录
     * @param string $uid 说话人
     * @param string $to 被说话人
     * @param string $content 说话内容
     * @param int|null $time 发送时间
     * @param array $params 额外数据
     * @return bool|mixed
     * @throws \Exception
     */
    public function addChatsGroupLog(string $uid, string $to, string $content, ?int $time = null, array $params = []) {

        return $this->addChatsLog($uid, $to, $content, 'group', $time, $params);
    }

    /**
     * 添加房间聊天记录
     * @param string $uid 说话人
     * @param string $to 被说话人
     * @param string $content 说话内容
     * @param int|null $time 发送时间
     * @param array $params 额外数据
     * @return bool|mixed
     * @throws \Exception
     */
    public function addChatsRoomLog(string $uid, string $to, string $content, ?int $time = null, array $params = []) {

        return $this->addChatsLog($uid, $to, $content, 'room', $time, $params);
    }

    /**
     * 添加系统通知聊天记录
     * @param string $uid 说话人
     * @param string $to 被说话人
     * @param string $content 说话内容
     * @param int|null $time 发送时间
     * @param array $params 额外数据
     * @param string $type 系统类型
     * @return bool|mixed
     * @throws \Exception
     */
    public function addChatsSystemLog(string $uid, string $to, string $content, ?int $time = null, array $params = [],string $type = '') {

        return $this->addChatsLog($uid, $to, $content, ('system@' . $type), $time, $params);
    }

    /**
     * 获取会话列表
     * @param string $uid
     * @param bool $loadList
     * @return array|bool
     * @throws \Exception
     */
    public function chatsMessageList(string $uid, bool $loadList = false) {

        $model = ChatsIdListModel::getInstance();

        $returnData = $model->chatsMessageList($uid, $loadList);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 未读消息列表
     * @param string $uid
     * @param bool $loadList
     * @return array|bool
     * @throws \Exception
     */
    public function unReadMessageList(string $uid, bool $loadList = true) {

        $model = ChatsIdListModel::getInstance();

        $returnData = $model->unReadMessageList($uid, $loadList);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 删除会话消息
     * @param string $uid 用户ID
     * @param string $chatsId 会话ID
     * @param bool $autoDel 是否自动清理聊天记录
     * @return bool
     * @throws \Exception
     */
    public function delChatsMessage(string $uid, string $chatsId, bool $autoDel = false) {

        $model = ChatsIdListModel::getInstance();

        $returnData = $model->delChatsMessage($uid, $chatsId, $autoDel);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 添加好友
     * @param string $uid uid
     * @param string $friendUid 被添加人uid
     * @param string $groupId 聊天分组id
     * @return bool|mixed
     * @throws \Exception
     */
    public function addFriends(string $uid, string $friendUid, string $groupId = '') {

        $model = ChatsFriendsModel::getInstance();

        $returnData = $model->addFriends($uid, $friendUid, $groupId);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 删除好友
     * @param string $uid 用户ID
     * @param string $friendUid 被删除好友用户ID
     * @param bool $eachOther 是否双向删除
     * @return bool
     * @throws \Exception
     */
    public function delFriends(string $uid, string $friendUid, bool $eachOther = false) {

        $model = ChatsFriendsModel::getInstance();

        $returnData = $model->delFriends($uid, $friendUid, $eachOther);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 好友移动分组
     * @param string $uid 用户ID
     * @param string|array $moveUidArr 需要移动的好友UID
     * @param string $moveGroupId 需要移动的分组ID
     * @return bool
     * @throws \Exception
     */
    public function friendsMoveGroup(string $uid, $moveUidArr, string $moveGroupId) {

        $model = ChatsFriendsModel::getInstance();

        $returnData = $model->friendsMoveGroup($uid, $moveUidArr, $moveGroupId);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 创建聊天好友分组数据
     * @param string $uid 用户ID
     * @param string $groupName 群组名称
     * @return array|bool|false|mixed|string
     * @throws \Exception
     */
    public function createFriendsGroup(string $uid, string $groupName) {

        $model = ChatsFriendsGroupModel::getInstance();

        $returnData = $model->createGroup($uid, $groupName);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 删除好友分组
     * @param string $uid 用户ID
     * @param string $groupId 好友分组ID
     * @param bool|null $handlingType 处理方式 null(不自动处理) true(移动好友后删除分组) false(删除好友后删除分组)
     * @return bool
     * @throws \Exception
     */
    public static function delFriendsGroup(string $uid, string $groupId, ?bool $handlingType = null) {

        $model = ChatsFriendsGroupModel::getInstance();

        $returnData = $model->delGroup($uid, $groupId, $handlingType);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 重命名好友分组
     * @param string $uid 用户ID
     * @param string $groupId 分组ID
     * @param string $groupName 重命名名称
     * @return bool
     */
    public function renameFriendsGroup(string $uid, string $groupId, string $groupName) {

        $model = ChatsFriendsGroupModel::getInstance();

        $returnData = $model->renameGroup($uid, $groupId, $groupName);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 好友分组排序
     * @param string $uid 用户ID
     * @param string $groupId 分组ID
     * @param int $sort 排序序列号 正序
     * @return bool
     */
    public function sortFriendsGroup(string $uid, string $groupId, int $sort = 0) {

        $model = ChatsFriendsGroupModel::getInstance();

        $returnData = $model->sortGroup($uid, $groupId, $sort);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 获取群成员列表
     * @param string $groupId 群ID
     * @return array|bool
     * @throws \Exception
     */
    public function getGroupMembersList(string $groupId) {

        $model = ChatsGroupMembersModel::getInstance();

        $returnData = $model->getGroupMembersList($groupId);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 获取群主信息
     * @param string $groupId
     * @return array|bool
     * @throws \Exception
     */
    public function getGroupManager(string $groupId) {

        $model = ChatsGroupModel::getInstance();

        $returnData = $model->getGroupManager($groupId);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 创建群聊
     * @param string $uid 管理用户ID
     * @param string $groupName 群ID
     * @return bool|mixed
     * @throws \Exception
     */
    public function createGroup(string $uid, string $groupName) {

        $model = ChatsGroupModel::getInstance();

        $returnData = $model->createGroup($uid, $groupName);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 删除群聊
     * @param string $groupId 群聊ID
     * @param bool $autoClear 是否自动清理用户
     * @return bool
     * @throws \Exception
     */
    public function delGroup(string $groupId, bool $autoClear = false) {

        $model = ChatsGroupModel::getInstance();

        $returnData = $model->delGroup($groupId, $autoClear);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 添加群组成员
     * @param string $groupId 群组ID
     * @param string $uid uid
     * @param int $manageLevel 管理级别
     * @return bool|mixed
     * @throws \Exception
     */
    public function addGroupMembers(string $groupId, string $uid, int $manageLevel = 0) {

        $model = ChatsGroupMembersModel::getInstance();

        $returnData = $model->addGroupMembers($groupId, $uid, $manageLevel);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 移除群成员
     * @param string $groupId 群ID
     * @param string $uid 操作人
     * @param string $removeUid 被移除人
     * @return bool
     * @throws \Exception
     */
    public function removeGroupMembers(string $groupId, string $uid, string $removeUid) {

        $model = ChatsGroupMembersModel::getInstance();

        $returnData = $model->removeGroupMembers($groupId, $uid, $removeUid);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

    /**
     * 退出群聊
     * @param string $groupId 群ID
     * @param string $uid 退出人
     * @return bool
     * @throws \Exception
     */
    public function exitGroupMembers(string $groupId, string $uid) {

        $model = ChatsGroupMembersModel::getInstance();

        $returnData = $model->exitGroupMembers($groupId, $uid);

        if ($returnData === false){
            return returnUtil()->error($model->getError());
        }

        return $returnData;
    }

}