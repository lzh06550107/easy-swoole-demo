<?php

use EasySwoole\Log\LoggerInterface;

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 8080,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time' => 3
        ],
        'TASK' => [ // easyswoole自定义任务，非swoole中任务
            'workerNum' => 4,
            'maxRunningNum' => 128,
            'timeout' => 15
        ]
    ],
    "LOG" => [
        'dir' => null,
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        'handler' => null,
        'logConsole' => true,
        'displayConsole'=>true,
        'ignoreCategory' => []
    ],
    'TEMP_DIR' => '/temp',
    'MYSQL'         => [
        'host'          => 'mysql',
        'port'          => 3306,
        'user'          => 'root',
        'password'      => 'root',
        'database'      => 'test',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'POOL_MAX_NUM'  => 20,
        'POOL_TIME_OUT' => 1
    ]
];
