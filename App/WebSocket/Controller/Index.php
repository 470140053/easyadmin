<?php

namespace App\WebSocket\Controller;

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class Index extends Base
{

    /**
     * 无需登录的方法
     * @var array
     */
    protected $noNeedLogin = [
        'reg'
    ];

    /**
     * 注册
     * @return bool
     */
    public function reg()
    {
        if ($this->verifyReg() === false) {
            return false;
        }
        if ($this->autoReg() === false) {
            return false;
        }
        return $this->success('注册成功!');
    }

    /**
     * 发送消息 (同 User类message()方法)
     * @return bool
     * @throws \Exception
     */
    public function send()
    {

        return $this->helpUser()->message();
    }
}
