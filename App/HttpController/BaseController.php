<?php

namespace App\HttpController;

use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\HttpAnnotation\AnnotationController;

// 全局基础控制器定义
class BaseController extends AnnotationController
{
    // 找不到控制器默认会调用这个方法
    public function index()
    {
        $this->actionNotFound('index');
    }

    /**
     * 获取用户的真实IP
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP($headerName = 'x-real-ip')
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');
        if ($clientAddress === '127.0.0.1') {
            if (!empty($xri)) {  // 如果有xri 则判定为前端有NGINX等代理
                $clientAddress = $xri[0];
            } elseif (!empty($xff)) {  // 如果不存在xri 则继续判断xff
                $list = explode(',', $xff[0]);
                if (isset($list[0])) {
                    $clientAddress = $list[0];
                }
            }
        }
        return $clientAddress;
    }

    /**
     * 获取请求参数
     * @param string $name 参数名称
     * @param null $default 参数默认值
     * @return array|mixed|object|null
     */
    protected function input($name, $default = null)
    {
        $value = $this->request()->getRequestParam($name);
        return $value ?? $default;
    }
}
