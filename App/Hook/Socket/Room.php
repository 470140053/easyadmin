<?php

/**
 * 房间钩子
 */
namespace App\Hook\Socket;

class Room{

    /**
     * 解除绑定
     * @param $fd
     * @return bool
     */
    public function hookDelBindFd($fd){
        $roomId = socketHelp()->fdGetRoomId($fd);
        if(empty($roomId)){
            return false;
        }
        $uid = socketHelp()->getUid($fd);
        $isLast = false;

        $mHelp = socketHelp()->roomHelp($roomId);
        //离开房间
        $mHelp->storage()->outRoom($fd,$isLast);
        if($isLast){
            $data = [
                'code'      => 200,
                'msg'       => '离开房间!',
                'class'     => 'Store',
                'action'    => 'outRoom',
                'time'      => socketHelp()->getMillisecond(),
                'data'      => [
                    'user_info' => socketHelp()->userInfo($uid),
                    'room_id'   => $roomId
                ]
            ];
            return $mHelp->push($data);
        }

        return true;
    }

    /**
     * 删除所有数据
     * @return bool
     */
    public function hookUnAllData(){
        $redis = eRedis();
        //删除所有userInfo信息
        $key = \App\Storage\RoomUser::baseTableName();
        $list = $redis->keys($key . '*');

        if(!is_array($list)){
            return false;
        }
        //删除key
        $redis->del(...array_values($list));
        return true;
    }

}