<?php

/**
 * 店铺钩子
 */
namespace App\Hook\Chats;

class Store {

    /**
     * 获取用户信息钩子
     * @param $hasId
     * @return array|bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function chatsUserInfo($hasId){

        $info = \App\Http\Admin\Model\StoreModel::create()->where(['id' => $hasId])->field(['title as nickname','thumb as avatar','null as thumb_text'])->get();

        if(empty($info)){
            return returnUtil()->error('房间信息获取失败!');
        }

        $info = $info->toArray(false,false);

        return [
            'nickname'      => $info['nickname'],
            'avatar'        => $info['thumb_text']
        ];
    }

}