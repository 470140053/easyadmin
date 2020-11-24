<?php

namespace App\Http\Addons\User\Controller\Api;


use App\Http\Api\Controller\ExteriorApi;

use App\Http\Addons\User\Model\UserModel;
use App\Http\Addons\User\Assemble\UserAssemble;

class User extends ExteriorApi
{

    /**
     * Undocumented function
     * 用户信息
     * @return void
     */
    public function getUserInfo()
    {
        $info = UserModel::getInfoById($this->data['user']['id'], ['*', 'null as avatar_text']);
        if (!empty($user)) {
            //这里是进入websocket的用户表，前端直接用户这里给的token注册socket
            $user['up_info'] = true;
            $user['token'] = (new \App\WebSocket\Model\Chats\ChatsUserModel())->fetchToken('user',$user['id'],$user);
        }

        return $this->success('ok', [
            'user' => $info
        ]);
    }

}