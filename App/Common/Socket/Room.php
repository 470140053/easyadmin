<?php


namespace App\Common\Socket;

class Room extends Base {

    private static $instance;

    /**
     * @param mixed ...$args
     * @return Room
     */
    public static function getInstance(...$args){
        $key = serialize($args);
        if(!isset(self::$instance[$key])){
            self::$instance[$key] = new static(...$args);
        }
        return self::$instance[$key];
    }

    /**
     * 房间ID
     * @var int
     */
    protected $_roomId = 0;

    public function __construct($roomId){
        $this->_roomId = $roomId;
    }

    /**
     * 推送房间消息
     * @param $data
     * @return bool
     */
    public function push($data){

        $storageRoomUser = $this->storage();
        if($storageRoomUser === false){
            return $this->error('房间信息获取失败!');
        }

        $socketHelp = socketHelp();

        $server = $socketHelp->swooleServer();

        $userAll = $storageRoomUser->getAll();

        foreach ($userAll as $userId => $userInfo) {
            $userInfo = $storageRoomUser->resetData($userInfo,false);
            $fds = $userInfo['fds'];
            if(empty($fds)){
                continue;
            }
            //推送消息
            $socketHelp->push($fds,$data,$server);
        }

        return true;
    }

    /**
     * 房间ID
     * @return int
     */
    public function roomId(){
        return $this->_roomId;
    }

    /**
     * 存储器
     * @return \App\Storage\RoomUser|bool
     */
    public function storage(){
        $roomId = $this->roomId();
        if(empty($roomId)){
            return $this->error('无效的房间!');
        }
        return \App\Storage\RoomUser::getInstance($roomId);
    }

    /**
     * 获取房间信息
     * @return array|bool|string
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getRoomData(){

        $roomId = $this->roomId();

        $key = 'socket_room:id_' . $roomId;

        $redis = eRedis();

        $roomData = $redis->hGetAll($key);

        if(!empty($roomData)){
            return $roomData;
        }

        $fields = [
            'id as room_id',
            'title as room_name',
            'thumb as room_logo'
        ];

        //查询房间信息
        $roomData = \App\Http\Admin\Model\StoreModel::create()->where(['id' => $roomId])->field($fields)->get();
        if(empty($roomData)){
            return $this->error('无效的房间!');
        }

        $roomData = $roomData->toArray(false,false);

        //存储 并设置超时时间
        $redis->hMSet($key,$roomData);
        $redis->expire($key,15 * 60);//15分钟超时

        return $roomData;
    }

    /**
     * 获取房间所有用户
     * @param bool $getInfo 获取info信息
     * @return array
     */
    public function getRoomUserAll(bool $getInfo = false){
        $storageRoomUser = $this->storage();
        if($storageRoomUser === false){
            return [];
        }

        $list = [];

        $userAll = $storageRoomUser->getAll();

        foreach ($userAll as $userId => $userInfo) {
            $userInfo = $storageRoomUser->resetData($userInfo,false);
            if($getInfo){
                $userInfo['info'] = socketHelp()->userInfo($userId);
            }
            $list[] = $userInfo;
        }

        return $list;
    }

    /**
     * 验证fd是否加入房间
     * @param $fd
     * @return bool
     */
    public function verifyFdJoin($fd){

        //获取是否加入房间
        $storage = $this->storage();
        if($storage === false){
            return $this->error('房间信息获取失败!');
        }

        $uid = socketHelp()->getUid($fd);

        if(empty($uid)){
            return $this->error('你未加入该房间!');
        }

        $roomUser = $storage->get($uid);

        $fds = !empty($roomUser['fds']) ? socketHelp()->socketFds($roomUser['fds']) : [];

        if(empty($roomUser) || !in_array($fd,$fds)){
            return $this->error('你未加入该房间!');
        }

        return true;
    }

    /**
     * 验证uid是否加入房间
     * @param $uid
     * @return bool
     */
    public function verifyUidJoin($uid){

        //获取是否加入房间
        $storage = $this->storage();
        if($storage === false){
            return $this->error('房间信息获取失败!');
        }

        $roomUser = $storage->get($uid);

        $fds = !empty($roomUser['fds']) ? socketHelp()->socketFds($roomUser['fds']) : [];

        if(empty($roomUser) || empty($fds)){
            return $this->error('你未加入该房间!');
        }

        return true;
    }

    /**
     * 房间用户列表
     * @return array
     */
    public function roomUserList(){
        $list = $this->getRoomUserAll();

        $has = 'user';

        $socketHelp = socketHelp();

        $userList = from($list ? $list : [])->where(function ($item) use($has){
            if($item['has'] == $has){
                return true;
            }
            return false;
        })->select(function ($item) use($socketHelp){
            $item['info'] = $socketHelp->userInfo($item['uid']);
            return $item;
        })->toList();

        return $userList;
    }

}