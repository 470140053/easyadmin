<?php
namespace App\Common;

use App\Http\Admin\Model\ToolsQueueModel;

/**
 * 队列
 */
class Queue{

    use \EasySwoole\Component\Singleton;

    /**
     * 添加队列
     * @param $class
     * @param $action
     * @param array $params
     * @param int $startTime
     * @param int $delay
     * @param int $mode
     * @return bool
     * @throws \Exception
     */
    public function add($class, $action, $params = [], $startTime = 0, $delay = 3, $mode = 0) {
        ToolsQueueModel::add(!empty($startTime) ? $startTime : time(), $class . "@" . $action, $params, $delay, $mode);
        return true;
    }

    /**
     * 获取队列
     * @param $class
     * @param $action
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function info($class, $action, $params = []) {
        return ToolsQueueModel::info($class . "@" . $action, $params);
    }

}