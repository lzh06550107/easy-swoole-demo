<?php

namespace App\HttpController\Api\Admin;

use App\HttpController\Api\ApiBase;
use App\Model\Admin\AdminModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

// 后台管理基础控制器定义
class AdminBase extends ApiBase
{
    //public才会根据协程清除
    public $who;
    //session的cookie头
    protected $sessionKey = 'adminSession';
    //白名单
    protected $whiteList = [];

    /**
     * onRequest
     * @param null|string $action
     * @return bool|null
     * @throws \Throwable
     * @author LZH
     */
    public function onRequest(?string $action): ?bool
    {
        if (parent::onRequest($action)) {
            //白名单判断，即不需要登录就可访问
            if (in_array($action, $this->whiteList)) {
                return true;
            }
            //获取登入信息
            if (!$this->getWho()) {
                $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入已过期');
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * getWho
     * @return bool
     * @author LZH
     */
    public function getWho(): ?AdminModel
    {
        if ($this->who instanceof AdminModel) {
            return $this->who;
        }
        $sessionKey = $this->request()->getRequestParam($this->sessionKey);
        if (empty($sessionKey)) { // 参数中没有，则从cookie中获取sessionId
            $sessionKey = $this->request()->getCookieParams($this->sessionKey);
        }
        if (empty($sessionKey)) {
            return null;
        }
        $adminModel = new AdminModel();
        $adminModel->adminSession = $sessionKey;
        $this->who = $adminModel->getOneBySession();
        return $this->who;
    }

    protected function getValidateRule(?string $action): ?Validate
    {
        return null;
        // TODO: Implement getValidateRule() method.
    }
}
