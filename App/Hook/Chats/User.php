<?php

/**
 * 用户钩子
 */
namespace App\Hook\Chats;

class User {

    /**
     * 获取用户信息钩子
     * @param $hasId
     * @return array|bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function chatsUserInfo($hasId){

        $info = \App\Http\Addons\User\Model\UserModel::create()->where(['id' => $hasId])->field(['username','nickname','avatar','null as avatar_text'])->get();

        if(empty($info)){
            return returnUtil()->error('用户信息获取失败!');
        }

        $info = $info->toArray();

        return [
            'nickname'      => !empty($info['nickname']) ? $info['nickname'] : $info['username'],
            'avatar'        => $info['avatar_text']
        ];
    }

}