<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 18-11-6
 * Time: 下午4:28
 */

namespace Crontab;


use App\Http\Admin\Model\ToolsQueueModel;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Task\TaskManager;

class TaskMinute extends AbstractCronTask
{
    public static function getRule(): string
    {
        //每分钟执行
        return '*/1 * * * *';
    }

    public static function getTaskName(): string
    {
        return  'TaskMinute';
    }

    function run(int $taskId, int $workerIndex)
    {
        $conf = Config::getInstance()->getConf('MAIN_SERVER');
        $domain = 'http://' . $conf['LISTEN_ADDRESS'] . (empty($conf['PORT']) || $conf['PORT'] == 80 ? '' : ':' . $conf['PORT']);
        TaskManager::getInstance()->async(function () use ($domain) {
            $step = 2;
            for ($i = 0; $i < 60; $i = ($i + $step)) {
                $config = ToolsQueueModel::CONFIG;
                if (!$config['status']) {
                    $this->echo('队列未开启 ');
                }
                $url = $domain . '/admin/index/queue';
                $status = ToolsQueueModel::thread($url, $config['every_num']);
                $date = date('Y-m-d H:i:s');
                if ($status == -1) {
                    $this->echo('锁定中 ' . $date);
                }
                if ($status == 1) {
                    $this->echo('执行完毕 ' . $date);
                }
                if ($status == 0) {
                    $this->echo('暂无任务 ' . $date);
                }
                //等待执行
                sleep($step);
            }
        });
    }


    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        echo $throwable->getMessage();
    }

    /**
     * 输出消息
     * @param string $msg
     */
    public function echo(string $msg)
    {
        echo $msg . PHP_EOL;
    }
}
