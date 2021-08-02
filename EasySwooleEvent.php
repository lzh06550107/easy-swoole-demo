<?php


namespace EasySwoole\EasySwoole;

use EasySwoole\Component\Di;
use EasySwoole\Component\Process\Manager;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Crontab\Crontab;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

    // https://www.easyswoole.com/QuickStart/notice.html
class EasySwooleEvent implements Event
{
    // 在 EasySwoole 的 bootstrap 事件、initialize 事件、mainServerCreate 事件都是在主进程中执行的，在这些事件中使用协程，必须使用调度器。
    // Worker 进程中使用协程，可以使用go函数

    // 在主进程中初始化程序全局生命周期对象
    public static function initialize()
    {
        //通过DI注入一个对象，其它地方都可以通过 Di 直接调用这个对象
        //这部分内存会在写时分离（COW），在 Worker 进程内对这些对象进行写操作时，会自动从共享内存中分离，变为进程全局对象
        //程序全局期 include/require 的代码，必须在整个程序 shutdown 时才会释放，reload 无效

        // 开发者自定义设置 错误级别
        Di::getInstance()->set(SysConst::ERROR_REPORT_LEVEL, E_ALL);

        // 开发者自定义设置 日志处理类(该类需要实现 \EasySwoole\Log\LoggerInterface，开发者可自行查看并实现，方便开发者自定义处理日志)
        $logDir = EASYSWOOLE_LOG_DIR; // 定义日志存放目录
        $loggerHandler = new \EasySwoole\Log\Logger($logDir); // 定义日志处理对象
        Di::getInstance()->set(SysConst::LOGGER_HANDLER, $loggerHandler);

        // 开发者自定义设置 Trace 追踪器(该类需要实现 \EasySwoole\Trigger\TriggerInterface，开发者可自行查看并实现，方便开发者自定义处理 Trace 链路)
        // Trace 追踪器需要依据上面的 logger_handler
        Di::getInstance()->set(SysConst::TRIGGER_HANDLER, new \EasySwoole\Trigger\Trigger($loggerHandler));

        // 开发者自定义设置 error_handler
        Di::getInstance()->set(SysConst::ERROR_HANDLER, function ($errorCode, $description, $file = null, $line = null) {
            // 开发者对错误进行处理
        });

        // 开发者自定义设置 shutdown
        Di::getInstance()->set(SysConst::SHUTDOWN_FUNCTION, function () {
            // 开发者对 shutdown 进行处理
        });

        // 开发者自定义设置 HttpException 全局处理器
        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, function ($throwable, Request $request, Response $response) {
            $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
            $response->write(nl2br($throwable->getMessage() . "\n" . $throwable->getTraceAsString()));
            Trigger::getInstance()->throwable($throwable);
        });

        // 开发者自定义设置 onRequest v3.4.x+
        // 实现 onRequest 事件
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_ON_REQUEST, function (Request $request, Response $response): bool {
            ###### 对请求进行拦截 ######
            // 不建议在这拦截请求，可增加一个控制器基类进行拦截

            ###### 处理请求的跨域问题 ######
            $response->withHeader('Access-Control-Allow-Origin', '*');
            $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->withHeader('Access-Control-Allow-Credentials', 'true');
            $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            if ($request->getMethod() === 'OPTIONS') {
                $response->withStatus(Status::CODE_OK);
                return false;
            }
            return true;
        });

        // 开发者自定义设置 afterRequest v3.4.x+
        Di::getInstance()->set(SysConst::HTTP_GLOBAL_AFTER_REQUEST, function (Request $request, Response $response): void {

            // 示例：获取此次请求响应的内容
//            TrackerManager::getInstance()->getTracker()->endPoint('request');
            $responseMsg = $response->getBody()->__toString();
            Logger::getInstance()->console('响应内容:' . $responseMsg);
            // 响应状态码：
            // var_dump($response->getStatusCode());

            // tracker 结束，结束之后，能看到中途设置的参数，调用栈的运行情况
//            TrackerManager::getInstance()->closeTracker();
        });

        // 注册数据库连接及连接池(详见：https://www.easyswoole.com/Components/Orm/install.html)
        $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('MYSQL'));
        DbManager::getInstance()->addConnection(new Connection($config));
        // 注册 Redis 连接及连接池(详见：https://www.easyswoole.com/Components/Redis/introduction.html)
    }

    // 在主进程中初始化进程全局生命周期对象
    public static function mainServerCreate(EventRegister $register)
    {

        //程序全局生命周期对象被控制器修改之后，该对象会复制一份出来到控制器所属的进程，这个对象只能被这个进程访问，其他进程访问的依旧是全局对象。
        //给服务注册 onWorkerStart 事件(在 EasySwooleEvent.php 中的 mainServerCreate 事件中进行注册 onWorkerStart 事件)时创建的对象，只会在该 Worker 进程才能获取到。
        //进程全局对象所占用的内存是在当前子进程内存堆的，并非共享内存。对此对象的修改仅在当前 Worker 进程中有效，进程全局期 include/require 的文件，在 reload 后就会重新加载

        // 注册Crontab
//        Crontab::getInstance()->addTask(CustomCrontab::class);

//        $processConfig = new \EasySwoole\Component\Process\Config([
//            'processName' => 'CustomProcess', // 设置 自定义进程名称
//            'processGroup' => 'Custom', // 设置 自定义进程组名称
//            'arg' => [
//                'arg1' => 'this is arg1!'
//            ], // 【可选参数】设置 注册进程时要传递给自定义进程的参数，可在自定义进程中通过 $this->getArg() 进行获取
//            'enableCoroutine' => true, // 设置 自定义进程自动开启协程
//        ]);
//
//        Manager::getInstance()->addProcess(new CustomProcess($processConfig));
    }

    // EasySwoole 主服务启动前调用协程必须初始化调度器来创建事件循环
}
