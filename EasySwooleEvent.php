<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Component\Di;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config as DbConfig;

use App\Http\HttpEvent;

use EasySwoole\EasySwoole\Crontab\Crontab;
use Crontab\TaskMinute;

use App\WebSocket\WebSocketEventConfig;


class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        $dbConf = Config::getInstance()->getConf('MYSQL');
        $config = new DbConfig($dbConf);
        DbManager::getInstance()->addConnection(new Connection($config));

        define('MYSQL_PREFIX', Config::getInstance()->getConf('MYSQL')['prefix']);

        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_NAMESPACE, 'App\\Http\\');
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, [HttpEvent::class, 'onRequest']);

        // 开始一个定时任务计划 
        // Crontab::getInstance()->addTask(TaskMinute::class);
    }

    public static function mainServerCreate(EventRegister $register)
    {
        $redisConfig = new \EasySwoole\Redis\Config\RedisConfig();
        // $redisConfig->setHost('111.231.207.239');
        // $redisConfig->setPort(6379);
        // $redisConfig->setAuth();
        //redis连接池注册(config默认为127.0.0.1,端口6379)
        $redisPoolConfig = \EasySwoole\RedisPool\Redis::getInstance()->register('redis', $redisConfig);
        //配置连接池连接数
        $redisPoolConfig->setMinObjectNum(5);
        $redisPoolConfig->setMaxObjectNum(20);
        $redisPoolConfig->setAutoPing(10); //设置自动ping的间隔 版本需>=2.1.2


        // 配置同上别忘了添加要检视的目录   热加载
        $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
        $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
        $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);
        $server = ServerManager::getInstance()->getSwooleServer();
        $hotReload->attachToServer($server);

        WebSocketEventConfig::mainServerCreate($register);
    }
}
