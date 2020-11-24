<?php

/**
 * 用户帮助文件
 */

namespace App\WebSocket\Help;


use EasySwoole\Component\Singleton;

abstract class Base
{

    use Singleton;

    protected $_class = null;

    /**
     * 模块帮助文件
     * @var null
     */
    protected $_mHelp = null;

    public function __construct(\App\WebSocket\Controller\Base $class)
    {
        $this->_class = $class;
    }

    /**
     * 获取参数
     * @return array
     */
    protected function getArgs()
    {
        return $this->_class->getArgs();
    }

    /**
     * 获取用户ID
     * @return int|mixed
     */
    protected function getUid()
    {
        return $this->_class->getUid();
    }

    protected function getData()
    {
        return $this->_class->getData();
    }

    /**
     * error
     * @param mixed ...$args
     * @return bool
     */
    protected function error(...$args)
    {
        return $this->_class->error(...$args);
    }

    /**
     * errorData
     * @param mixed ...$args
     * @return array|bool|false
     */
    protected function errorData(...$args)
    {
        return $this->_class->errorData(...$args);
    }

    /**
     * success
     * @param mixed ...$args
     * @return bool
     */
    protected function success(...$args)
    {
        return $this->_class->success(...$args);
    }

    /**
     * successData
     * @param mixed ...$args
     * @return array|bool|false
     */
    protected function successData(...$args)
    {
        return $this->_class->successData(...$args);
    }

    /**
     * 用户信息
     * @return array
     */
    protected function userInfo()
    {
        return $this->_class->userInfo();
    }

    /**
     * 获取fd
     * @return mixed|null
     */
    public function getFd()
    {
        return $this->_class->getFd();
    }

    /**
     * 绑定fd
     * @param $uid
     * @return bool
     */
    public function bindFd($uid)
    {
        return $this->_class->bindFd($uid);
    }

    /**
     * 验证注册
     * @return bool
     */
    public function verifyReg()
    {
        return $this->_class->verifyReg();
    }

    /**
     * 自动注册服务
     * @return bool
     */
    public function autoReg()
    {
        return $this->_class->autoReg();
    }

    /**
     * 设置扩展参数
     * @param array $data
     * @return array
     */
    public function setExtendData(array $data)
    {
        return $this->_class->setExtendData($data);
    }
}
