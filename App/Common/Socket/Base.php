<?php


namespace App\Common\Socket;

abstract class Base{

    /**
     * 推送到fd
     * @param $fds
     * @param $data
     * @return bool
     */
    public function pushFd($fds,$data){
        return socketHelp()->push($fds,$data);
    }

    /**
     * error
     * @param string $msg
     * @param int $code
     * @return bool
     */
    protected function error($msg = '', $code = 500){
        return returnUtil()->error($msg,$code);
    }

    /**
     * 获取code
     * @return int
     */
    public function getCode() {
        return returnUtil()->getCode();
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError() {
        return returnUtil()->getError();
    }

    /**
     * 成功返回
     * @param bool $data
     * @param int $code
     * @return bool
     */
    protected function success($data = true,$code = 200) {
        return returnUtil()->success($data,$code);
    }


}