<?php

/**
 * 用户钩子
 */
namespace App\Hook\Socket;

class User{

    /**
     * 删除所有数据
     * @return bool
     */
    public function hookUnAllData(){
        //删除所有userInfo信息
        \App\Storage\User::getInstance()->deleteAll();
        return true;
    }

}