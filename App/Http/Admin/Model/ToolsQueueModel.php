<?php

namespace App\Http\Admin\Model;

use Extend\Base\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;
use \EasySwoole\RedisPool\Redis;
use EasySwoole\ORM\Utility\Schema\Table;
use App\Http\Admin\Assemble\ToolsQueueAssemble;

class ToolsQueueModel extends BaseModel
{

    //对应的表名
    protected $tableName = MYSQL_PREFIX . 'tools_queue';

    //主键字段
    public $primaryKey = 'queue_id';

    protected $assemble = ToolsQueueAssemble::class;


    const CONFIG = [
        'lock_time' => '300',
        'every_num' => '10',
        'retry_num' => '3',
        'secret'    => 'queue_task',
        'status'    => 1
    ];

    const LOC_KEY = 'queue_lock';

    protected static function getTimeAttr($value)
    {

        return  !empty($value) ? (string) date('Y-m-d H:i:s', $value) : '';
    }


    protected static function getModeTextAttr($value, $data)
    {

        return (!empty($data['mode']) && $data['mode'] == 1) ? '循环' : '单次';
    }


    /**
     * 表的获取
     * 此处需要返回一个 EasySwoole\ORM\Utility\Schema\Table
     * @return Table
     */
    public function schemaInfo(bool $isCache = true): Table
    {
        $table = new Table($this->tableName);
        $table->colInt('queue_id')->setIsPrimaryKey(true);
        $table->colInt('time', 10);
        $table->colInt('num', 10);
        $table->colInt('delay', 10);
        $table->colTinyInt('mode', 1);
        $table->colTinyInt('status', 1);
        $table->colVarChar('class', 255);
        $table->colVarChar('mode_text', 255);
        $table->colText('args');

        return $table;
    }



    /**
     * Undocumented function
     * 分页数据
     * @param array $map
     * @param integer $page
     * @param integer $total
     * @param array $field
     * @param array $order
     * @return array
     */
    public static function getListByPage(array $map, int $page, int $total, array $field = ['*', 'null as mode_text']): array
    {

        $model = self::create()->limit((($page - 1) * $total), $total)->withTotalCount();

        $list = $model->where($map)->field($field)->order('queue_id', 'DESC')->all(null);

        $result = $model->lastQueryResult();
        // 总条数
        $totalCount = $result->getTotalCount();

        return [$list, $totalCount];
    }







    /**
     * 任务列表
     * @param int $type 0未执行 1队列中
     * @param int $offet
     * @param int $limit
     */
    public static function getList($type = 0, $offet = 0, $limit = 10)
    {
        if (!$type) {
            $list = self::create()->where(['status' => 0])->limit($offet, $limit)->all();
        } else {
            $list = self::create()->where(['status' => 1])->limit($offet, $limit)->all();
        }
        return $list;
    }

    /**
     * 任务数量
     * @param int $type 0未执行 1队列中
     * @param int $startTime 开始时间
     * @param int $stopTime 结束时间
     * @return int
     */
    // public static function getCount($type = 0, $startTime = 0, $stopTime = 0) {
    //     if (!$type) {
    //         return self::create()->where(['status' => 0])->where("(time>={$startTime} and time<={$stopTime})")->count();
    //     } else {
    //         return self::create()->where(['status' => 1])->count();
    //     }
    // }

    /**
     * 添加队列
     * @param $time
     * @param $class
     * @param array $args
     * @param int $delay
     * @param int $mode
     * @return int
     */
    public static function add($time, $class, $args = [], $delay = 5, $mode = 0)
    {
        return self::create([
            'time'  => $time,
            'class' => $class,
            'args'  => json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'num'   => 0,
            'delay' => $delay,
            'mode'  => $mode
        ])->save();
    }

    /**
     * 获取队列信息
     * @param $class
     * @param array $args
     */
    public static function info($class, $args = [])
    {
        $info = self::create()->where(['class' => $class, 'args' => json_encode($args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)])->get();
        return !empty($info) ? $info->toArray() : null;
    }

    /**
     * 执行队列
     * @param string $url 队列Url
     * @param int $concurrent 进程数量
     * @param int $timeout 队列超时，秒
     * @return int
     */
    public static function thread($url, int $concurrent = 10, int $timeout = 30)
    {
        if (self::hasRedisLock()) {
            return -1;
        }
        self::redisLock($timeout);

        $taskList = self::create()->where(['time' => [time(), '<='], 'num' => [self::CONFIG['retry_num'], '<']])->limit($concurrent)->order('time', 'asc')->all();

        if (empty($taskList)) {
            self::unRedisLock();
            return 0;
        }
        $taskIds = array_column($taskList, 'queue_id');
        $taskCount = count($taskIds);
        self::create()->update(['status' => 1], ['queue_id' => [$taskIds, 'in']]);

        $client = new \GuzzleHttp\Client();
        $requests = function ($taskIds) use ($url) {
            foreach ($taskIds as $id) {
                // yield new \GuzzleHttp\Psr7\Request('GET', $url . '?secret=' . self::CONFIG['secret'] . '&id=' . $id);
                writeLog($url . '/' . $id . '/' . self::CONFIG['secret']);
                yield new \GuzzleHttp\Psr7\Request('GET', $url . '/' . $id . '/' . self::CONFIG['secret']);
            }
        };

        $pool = new \GuzzleHttp\Pool($client, $requests($taskIds), [
            'concurrency' => $taskCount,
            'fulfilled' => function ($response, $index) {
                $reason = $response->getStatusCode();
                if ($reason <> 200) {
                    writeLog('Task Error:');
                    return;
                }
                $contents = $response->getBody()->getContents();

                $data = json_decode($contents, true);
                if (empty($data)) {
                    writeLog('Task Error:Empty Return');
                    return;
                }
                if ($data['code'] <> 200) {
                    writeLog("Task Error: [{$data['code']}] {$data['message']}");
                    return;
                }
            },
            'rejected' => function ($reason, $index) {
                writeLog('Task:Request failed');
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        self::unRedisLock();
        return 1;
    }


    protected static function index(int $id)
    {

        if (!self::CONFIG['status']) {
            return false;
        }

        ToolsQueueModel::execute(self::CONFIG['secret'], $id, function ($task) {
            try {
                [$classStr, $action] = explode('@', $task['class'], 2);
                if (!class_exists($classStr)) {
                    writeLog('Task: ' . (!empty($classStr) ? $classStr : 'Null') . ' class does not exist', 'error');
                    return true;
                }
                $class = new $classStr();
                if (!method_exists($class, $action)) {
                    writeLog('Task: ' . (!empty($task['class']) ? $task['class'] : 'Null') . ' action does not exist', 'error');
                    return true;
                }
                return call_user_func_array([$class, $action], $task['args']);
            } catch (\Exception $e) {
                writeLog('Task: ' . $e->getMessage(), 'error');
                return false;
            }
        }, self::CONFIG['retry_num']);
    }

    /**
     * 任务执行
     * @param string $secret
     * @param int $id
     * @param callable $callback
     * @param int $retry
     */
    public static function execute($secret, $id, callable $callback, $retry = 3)
    {
        if (self::CONFIG['secret'] <> $secret) {
            return false;
        }
        if (empty($id)) {
            return false;
        }
        $task = self::create()->where(['queue_id' => $id, 'status' => 1])->get();
        if (empty($task)) {
            return true;
        }
        $task = $task->toArray();
        $task['args'] = json_decode($task['args'], true);
        if ($callback($task) === true) {
            self::create()->destroy(['queue_id' => $id]);
            return true;
        }
        $data = [
            'time'  => time() + $task['delay'],
            'class' => $task['class'],
            'args'  => json_encode($task['args'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'delay' => $task['delay'],
            'mode'  => $task['mode'],
            'num'   => QueryBuilder::inc(1),
        ];
        if (!$task['mode']) {
            if ($task['num'] < $retry) {
                self::create()->update($data, ['queue_id' => $id]);
            }
        } else {
            self::create()->update($data, ['queue_id' => $id]);
        }
        return true;
    }

    private static function redis()
    {
        return Redis::defer('redis');
    }

    /**
     * 设置锁
     * @param int $time
     */
    private static function redisLock($time = 30)
    {
        return self::redis()->set(self::LOC_KEY, 1, $time);
    }

    /**
     * 获取锁
     * @return bool|mixed|string
     */
    private static function hasRedisLock()
    {
        return self::redis()->get(self::LOC_KEY);
    }

    /**
     * 卸载锁
     * @return int
     */
    private static function unRedisLock()
    {
        return self::redis()->del(self::LOC_KEY);
    }


    public static function deleteInfo(int $id): bool
    {
        return self::create()->destroy($id);
    }
}
