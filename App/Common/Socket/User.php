<?php

/**
 * 用户帮助类
 */
namespace App\Common\Socket;

class User extends Base {

    use \EasySwoole\Component\Singleton;

    /**
     * 推送到uid
     * @param $uids
     * @param $data
     * @return bool
     */
    public function push($uids,$data){
        return socketHelp()->pushUid($uids,$data);
    }

    /**
     * 存储器
     * @return \App\Storage\User
     */
    public function storage(){
        return \App\Storage\User::getInstance();
    }

    /**
     * 获取用户信息
     * @param int $uid
     * @param bool $load
     * @return array
     */
    public function userInfo(int $uid,bool $load = false) : array {

        $userStorage = $this->storage();

        $userInfo = $userStorage->get($uid);
        if(empty($userInfo) || $load){
            $userInfo = \App\WebSocket\Model\Chats\ChatsUserModel::getInstance()->userInfo($uid);
            if(!empty($userInfo)){
                //设置缓存
                $userStorage->set($uid,$userInfo);
                //重新获取格式
                $userInfo = $userStorage->get($uid);
            }else{
                $userInfo = [];
            }
        }

        if(is_null($userInfo)){
            $userInfo = [];
        }

        return $userInfo;
    }

    /**
     * 删除用户信息
     * @param int $uid
     * @return bool
     */
    public function delUserInfo(int $uid){
        $this->storage()->delete($uid);
        return true;
    }

}