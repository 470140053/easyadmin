<?php
namespace App\Common;

/**
 * 返回信息公共类
 */
class ReturnUtil{

    use \EasySwoole\Component\Singleton;

    protected $code = 200;

    protected $error = '';

    /**
     * 失败返回
     * @param string $msg
     * @param int $code
     * @return bool
     */
    public function error($msg = '', $code = 500) {
        $this->error = $msg;
        $this->code = $code;
        return false;
    }

    /**
     * 获取code
     * @return int
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * 成功返回
     * @param bool $data
     * @param int $code
     * @return bool
     */
    public function success($data = true,$code = 200) {
        $this->code = $code;
        return $data;
    }

}