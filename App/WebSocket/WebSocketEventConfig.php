<?php

/**
 * Created by PhpStorm.
 * User: Apple
 * Date: 2018/11/1 0001
 * Time: 14:47
 */

namespace App\WebSocket;

use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Socket\Dispatcher;
use App\WebSocket\WebSocketEvent;
use App\WebSocket\WebSocketParser;
use EasySwoole\ORM\DbManager;

class WebSocketEventConfig
{
    public static function mainServerCreate($register) {
        /**
         * **************** websocket控制器 **********************
         */
         // 创建一个 Dispatcher 配置
         $conf = new \EasySwoole\Socket\Config();
         // 设置 Dispatcher 为 WebSocket 模式
         $conf->setType(\EasySwoole\Socket\Config::WEB_SOCKET);
         // 设置解析器对象
         $conf->setParser(new WebSocketParser());
         // 创建 Dispatcher 对象 并注入 config 对象
         $dispatch = new Dispatcher($conf);
 
         // 给server 注册相关事件 在 WebSocket 模式下  on message 事件必须注册 并且交给 Dispatcher 对象处理
         $register->set(EventRegister::onMessage, function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) use ($dispatch) {
             $dispatch->dispatch($server, $frame->data, $frame);
         });


         
         $websocketEvent = new WebSocketEvent();
         //自定义服务开启事件
         $register->set(EventRegister::onStart, function (\swoole_server $server) use ($websocketEvent) {
             go(function () use($websocketEvent,$server){
                 $websocketEvent->onStart($server);
             });
         });
         
        //自定义握手事件
         $register->set(EventRegister::onHandShake, function (\swoole_http_request $request, \swoole_http_response $response) use ($websocketEvent) {
            $websocketEvent->onHandShake($request, $response);
         });
         //自定义关闭事件
         $register->set(EventRegister::onClose, function (\swoole_server $server, int $fd, int $reactorId) use ($websocketEvent) {
             $websocketEvent->onClose($server, $fd, $reactorId);
         });
 
         //自定义服务终止事件
         $register->set(EventRegister::onShutdown, function (\swoole_server $server) use ($websocketEvent) {
             go(function () use($websocketEvent,$server){
                 $websocketEvent->onShutdown($server);
             });
         });

        

       
        $register->add($register::onWorkerStart,function (){
            //链接预热
            DbManager::getInstance()->getConnection()->getClientPool()->keepMin();
        });
    }
}