<?php

namespace App\WebSocket\Controller;

use EasySwoole\Socket\AbstractInterface\Controller;

/**
 * Class Index
 *
 * 此类是默认的 websocket 消息解析后访问的 控制器
 *
 * @package App\WebSocket
 */
class Base extends Controller
{

    /**
     * 错误码
     * @var int
     */
    protected $code = 0;

    protected $msg = '';


    /**
     * 返回数据体
     * @var array
     */
    protected $return_data = [];

    /**
     * 附加参数
     * @var array
     */
    protected $extend_data = [];

    /**
     * 无需登录的方法,同时也就不需要鉴权了
     * @var array
     */
    protected $noNeedLogin = [];


    /**
     * 返回false的时候为拦截
     * @param string|null $actionName
     * @return bool
     */
    protected function onRequest(?string $actionName): bool
    {
        if (!parent::onRequest($actionName)) {
            return false;
        }

        if (!$this->match($this->noNeedLogin, $actionName)) {
            if ($this->isLogin() === false) {
                return false;
            }
        }

        //自定义鉴权
        if (method_exists($this, '_auth')) {
            if ($this->_auth() === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检测当前控制器和方法是否匹配传递的数组
     * @param array $arr
     * @param string $action
     * @return bool
     */
    protected function match(array $arr, string $action): bool
    {

        if (empty($arr)) {
            return false;
        }

        // 是否存在
        if (in_array($action, $arr) || in_array('*', $arr)) {
            return true;
        }

        // 没找到匹配
        return false;
    }

    /**
     * 获取action
     * @return string|null
     */
    protected function getAction()
    {
        return $this->caller()->getAction();
    }

    /**
     * 获取客户端
     * @return \EasySwoole\Socket\Client\WebSocket|null
     */
    protected function getClient(): ?\EasySwoole\Socket\Client\WebSocket
    {
        return $this->caller()->getClient();
    }

    /**
     * 获取传入参数
     * @return array
     */
    public function getArgs()
    {
        return $this->caller()->getArgs();
    }

    /**
     * 获取fd
     * @return mixed|null
     */
    public function getFd()
    {
        return $this->getClient()->getFd();
    }

    /**
     * 获取客户端信息
     * @param int $fd
     * @return array
     */
    protected function getClientInfo($fd = 0)
    {
        if (empty($fd)) {
            $fd = $this->getFd();
        }
        return socketHelp()->getClientInfo($fd);
    }

    /**
     * 获取uid
     * @return int|mixed
     */
    public function getUid()
    {
        $info = $this->getClientInfo();
        return isset($info['uid']) ? $info['uid'] : 0;
    }

    /**
     * 绑定
     * @param $fd
     * @param $uid
     * @return mixed
     */
    protected function bind($fd, $uid)
    {
        return swooleServer()->bind($fd, $uid);
    }

    /**
     * 绑定fd
     * @param int $uid
     * @return bool
     */
    public function bindFd(int $uid)
    {
        $fd = $this->getFd();
        $this->bind($fd, $uid);
        return socketHelp()->bindFd($fd, $uid);
    }

    /**
     * 获取data值
     * @return array|bool
     */
    public function getData()
    {
        $raw = $this->getClient()->getData();
        $data = isJson($raw, true);
        if ($data === false) {
            return $this->error('decode message error!');
        }

        return $data;
    }

    /**
     * 用户信息
     * @return array
     */
    public function userInfo()
    {
        $uid = $this->getUid();
        return socketHelp()->userInfo($uid);
    }

    /**
     * 设置附加参数
     * @param array $data
     * @return array
     */
    public function setExtendData(array $data = [])
    {
        if (!empty($data)) {
            $this->extend_data = array_merge($this->extend_data, $data);
        }
        return $this->extend_data;
    }

    /**
     * 获取发送消息
     * @return array|false
     */
    public function sendData()
    {

        $data = $this->getData();

        if ($data === false) {
            return false;
        }

        if (empty($data)) {
            $data = [];
        }

        $data = array(
            'code'   => $this->code,
            'msg'    => $this->msg,
            'class'  => isset($data['class']) ? $data['class'] : 'index',
            'action' => isset($data['action']) ? $data['action'] : 'index',
            'time'   => socketHelp()->getMillisecond(),
            'data'   => $this->return_data
        );

        $data = array_merge($this->extend_data, $data);

        return $data;
    }

    /**
     * 发送消息
     */
    public function sendAjax()
    {

        $data = $this->sendData();

        $this->response()->setMessage(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }


    /**
     * 返回错误
     * @param null $msg
     * @param int $code
     * @param array $returnData
     * @return bool
     */
    public function error($msg = null, $code = 500, ?array $returnData = null)
    {
        $this->errorData($msg, $code, $returnData, false);
        $this->sendAjax();
        return false;
    }

    /**
     * error data
     * @param null $msg
     * @param int $code
     * @param array $returnData
     * @param bool $return
     * @return array|bool
     */
    public function errorData($msg = null, $code = 500, ?array $returnData = null, bool $return = true)
    {
        if (is_null($msg)) {
            $returnUtil = returnUtil();
            $this->code = $returnUtil->getCode();
            $this->msg = $returnUtil->getError();
        } else {
            $this->code = $code;
            $this->msg = $msg;
        }

        if (is_null($returnData)) {
            $returnData = $this->getArgs();
        }

        $this->return_data = $returnData;
        return $return ? $this->sendData() : true;
    }

    /**
     * 返回成功
     * @param string $msg
     * @param array $returnData
     * @param int $code
     * @return bool
     */
    public function success($msg = 'ok', ?array $returnData = null, $code = 200)
    {
        $this->successData($msg, $returnData, $code, false);
        $this->sendAjax();
        return true;
    }

    /**
     * 成功data
     * @param string $msg
     * @param array $returnData
     * @param int $code
     * @param bool $return
     * @return array|bool
     */
    public function successData($msg = 'ok', ?array $returnData = null, $code = 200, bool $return = true)
    {
        $this->code = $code;
        $this->msg = $msg;

        if (is_null($returnData)) {
            $returnData = $this->getArgs();
        }

        $this->return_data = $returnData;
        return $return ? $this->sendData() : true;
    }

    /**
     * 验证注册
     * @return bool
     */
    public function verifyReg()
    {
        $bindUid = $this->getUid();

        if (!empty($bindUid)) {
            return $this->error('请勿重复注册!');
        }
        return true;
    }

    /**
     * 自动注册服务
     * @return bool
     */
    public function autoReg()
    {
        $args = $this->getArgs();

        $bindUid = $this->getUid();
        if (empty($bindUid)) {
            $token = isset($args['token']) ? $args['token'] : 0;
            if (empty($args) || empty($token)) {
                return $this->error('sokect注册错误，参数错误!', 100);
            }

            if ($this->verifyReg() === false) {
                return false;
            }

            //验证token
            $returnData = socketHelp()->verifyToken($token, true);

            if ($returnData === false) {
                return $this->error();
            }

            $uid = $returnData['data']['uid'];

            //绑定fd
            $bingFd = $this->bindFd($uid);

            $userInfo = $this->userInfo();

            //设置附加参数
            $this->setExtendData([
                'user_info'     => $userInfo
            ]);

            return $bingFd;
        }

        return true;
    }

    /**
     * 是否登录
     * @return bool
     */
    public function isLogin()
    {
        $uid = $this->getUid();

        if (empty($uid)) {
            return $this->error('请先登录!');
        }

        return true;
    }

    /**
     * user帮助文件
     * @return \App\WebSocket\Help\User
     */
    protected function helpUser()
    {
        return \App\WebSocket\Help\User::getInstance($this);
    }

    /**
     * 房间帮助文件
     * @return \App\WebSocket\Help\Room
     */
    protected function helpRoom()
    {
        return \App\WebSocket\Help\Room::getInstance($this);
    }

    /**
     * 群组帮助文件
     * @return \App\WebSocket\Help\Group
     */
    protected function helpGroup()
    {
        return \App\WebSocket\Help\Group::getInstance($this);
    }
}
