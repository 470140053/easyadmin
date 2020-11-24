<?php

/**
 * 会话聊天记录表
 */

namespace App\WebSocket\Model\Chats;

use Extend\Base\BaseModel;

class ChatsLogModel  extends BaseModel {

    use \EasySwoole\Component\Singleton;


    //对应的表名
    protected $tableName = 'tp_chats_log';

    //主键字段
    public $primaryKey = 'log_id';

    /**
     * 添加聊天记录
     * @param string $uid 发送人
     * @param string $to 被发送人
     * @param string $content 聊天内容
     * @param string $type 聊天类型
     * @param int|null $time
     * @param array $params 额外数据
     * @return bool|mixed
     * @throws \Exception
     */
    public function addLog(string $uid, string $to, string $content, string $type, ?int $time = null, array $params = []) {

        if (empty($uid) || empty($to) || empty($content)) {
            return $this->error('参数错误!');
        }

        //获取发送人信息
        $info = socketHelp()->userInfo($uid);

        $chatsId = null;

        $isSystem = false;
        //系统基础方法
        $systemFunName = 'typeSystem';

        switch ($type) {
            case 'user':
                $toInfo = socketHelp()->userInfo($to);

                if(empty($toInfo)){
                    return $this->error('该用户不存在!');
                }

                if(empty($info)){
                    return $this->error('非法操作!');
                }

                $chatsId = socketHelp()->userChatsId($uid, $to, true);
                break;

            case 'group':
                $chatsId = socketHelp()->groupChatsId($to, true);
                break;

            case 'room':
                $chatsId = socketHelp()->roomChatsId($to, true);
                break;

            default:
                //系统消息
                if(strpos($type,'system@') === 0){
                    $typeArr = explode('@',$type,2);
                    $type = $typeArr[0] . ucfirst($typeArr[1]);
                    if(count($typeArr) == 2){
                        $chatsId = socketHelp()->systemChatsId($uid, $to,$typeArr[1], true);
                    }

                    $isSystem = true;
                }

                break;
        }

        if (is_null($chatsId)) {
            return $this->error('无效的会话类型!');
        }

        $data = [
            'uid'         => $uid,
            'chats_id'    => $chatsId,
            'nickname'    => $info['nickname'],
            'avatar'      => $info['avatar'],
            'content'     => $content,
            'create_time' => empty($time) ? socketHelp()->getMillisecond() : $time
        ];

        $data = array_merge($params, $data);

        if(empty($data['uid'])){
            return $this->error('uid不能为空!');
        }

        if(empty($data['chats_id'])){
            return $this->error('会话ID不能为空!');
        }

        //字符截取
        $description = cutString($content, 250);

        $class = ChatsIdModel::getInstance();

        //更新会话记录
        $class::create()->where(['key' => $chatsId])->update(['description' => $description, 'update_time' => time()]);

        $type = ucfirst($type);

        $method = "type{$type}";

        if (method_exists($class, $method)) {
            call_user_func([$class, $method], $uid, $to, $chatsId);
        }else if($isSystem){
            call_user_func([$class, $systemFunName], $uid, $to, $chatsId);
        }

        return self::create()->data($data)->save();
    }


    /**
     * 获取聊天记录
     * @param array $where
     * @param int $limit
     * @param array|string $order
     * @return array|bool
     * @throws \Exception
     */
    public function getLogList(array $where, $limit = 0, $order = ['create_time','desc']) {

        if (empty($where)) {
            return $this->error('参数错误!');
        }

        $fields = [
            $this->primaryKey,
            'uid',
            'nickname',
            'avatar',
            'type',
            'content',
            'ext',
            'is_recall',
            'create_time'
        ];

        if(!is_array($limit)){
            $limit = [$limit];
        }

        $list = self::create()->where($where)->field($fields)->order(...$order)->limit(...$limit)->all();

        $targetUser = ChatsUserModel::getInstance();

        foreach ($list as &$logData) {
            $logData['avatar'] = $targetUser->getAvatar($logData['avatar']);

            if ($logData['is_recall']) {
                $logData['content'] = $logData['nickname'] . ' 撤回一条消息!';
            }
        }

        unset($logData);

        return $list;
    }


    /**
     * 撤回消息
     * @param array $where
     * @return array|bool
     */
    public function withdrawMessage(array $where) {

        $info = $this->where($where)->field([$this->primaryKey, 'chats_id', 'uid', 'is_recall', 'create_time'])->get();

        $info = !empty($info) ? $info->toArray() : [];

        if (empty($info)) {
            return $this->error('无法撤回该条消息!');
        }

        if ($info['is_recall']) {
            return $this->error('该条消息已撤回!');
        }

        $is_recall = 1;

        $info['is_recall'] = $is_recall;

        $up = self::create()->where([$this->primaryKey => $info[$this->primaryKey]])->update(['is_recall' => $is_recall]);

        if (!$up) {
            return $this->error('撤回失败!');
        }

        return $info;
    }

    /**
     * 历史聊天记录列表
     * @param string $chatsId
     * @param int|null $time
     * @param int $limit
     * @return array|bool
     * @throws \Exception
     */
    public function historyMessageList(string $chatsId, ?int $time = null, $limit = 0) {

        if (is_null($time)) {
            $time = time();
        }

        $where = [
            'chats_id'    => $chatsId,
            'create_time' => [$time,'<']
        ];

        return $this->getLogList($where, $limit);
    }

    /**
     * 自动删除聊天记录
     * @param string $chatsId 会话ID
     * @param bool $forceDel 是否强制删除
     * @return bool
     * @throws \Exception
     */
    public function autoDelLog(string $chatsId, bool $forceDel = false) {

        if ($chatsId == '') {
            return $this->error('参数错误!');
        }

        $model = ChatsIdListModel::getInstance();

        $isDel = false;

        //存在聊天记录
        if ($model::create()->where(['chats_id' => $chatsId])->count()) {
            if ($forceDel) {
                //强制删除
                $model::create()->where(['chats_id' => $chatsId])->destroy();
                $isDel = true;
            }
        } else {
            $isDel = true;
        }

        if ($isDel) {
            //删除聊天记录
            self::create()->where(['chats_id' => $chatsId])->destroy();
            //删除会话ID
            ChatsIdModel::create()->where(['key' => $chatsId])->destroy();
        }

        return true;
    }

}