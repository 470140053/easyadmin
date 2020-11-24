<?php

namespace App\Http\Admin\Controller;

use App\Http\EmptyController;
use App\Http\Admin\Model\ToolsQueueModel;

class Index  extends EmptyController
{

    /**
     * Undocumented function
     * 后台页面地址
     * @return void
     */
    public function login()
    {
        $file = EASYSWOOLE_ROOT . '/tpl/admin/index.html';
        $this->response()->write(file_get_contents($file));
    }



    private function error($msg, $code = 500)
    {
        return $this->writeJson($code, [], $msg);
    }

    private function success($result, $msg = 'ok', $code = 200)
    {
        return $this->writeJson($code, $result, $msg);
    }

    /**
     * 队列调用
     * @throws \Exception
     */
    public function queue()
    {
        $get = $this->request()->getRequestParam();
        if (empty($get['secret']) || empty($get['id'])) {
            $this->error('ERROR: Queue');
            return false;
        }
        $secret = $get['secret'];
        $id = $get['id'];
        if (!$id) {
            $this->error('ERROR: Queue.' . $id);
            return false;
        }
        $config = ToolsQueueModel::CONFIG;
        if ($config['secret'] <> $secret) {
            $this->error('ERROR: Queue.' . $secret);
            return false;
        }
        if (!$config['status']) {
            exit('ERROR: Queue State closed');
        }
        ToolsQueueModel::execute($secret, $id, function ($task) {
            [$classStr, $action] = explode('@', $task['class'], 2);
            if (!class_exists($classStr)) {
                writeLog('Task: ' . ($classStr ?: 'Null') . ' class does not exist');
                return true;
            }
            $class = new $classStr();
            if (!method_exists($class, $action)) {
                writeLog('Task: ' . ($task['class'] ?: 'Null') . ' action does not exist');
                return true;
            }
            return call_user_func_array([$class, $action], $task['args']);
        }, $config['retry_num']);
        $this->success([], 'SUCCESS');
        return true;
    }
}
