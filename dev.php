<?php
return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9508,
        // 'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SERVER_TYPE' => EASYSWOOLE_WEB_SOCKET_SERVER,
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time'=>3
        ],
        'TASK'=>[
            'workerNum'=>4,
            'maxRunningNum'=>128,
            'timeout'=>15
        ]
    ],
    'TEMP_DIR' => null,
    'LOG_DIR' => null,

    'MYSQL'       => [
        'host'     => '127.0.0.1',
        'port'     => '8889',
        'user'     => 'root',
        'password' => 'root',
        'database' => 'easyadmin_v1',
        'prefix'   => 'tp_',
        'timeout'  => 30,
        'charset'  => 'utf8mb4',
    ],
    'USE'       => [
        //基础签名字段
        'safe_key'      => 'bc5ff21a561b7998',
        'domain'        => 'http://127.0.0.1:9511'
    ]
];
