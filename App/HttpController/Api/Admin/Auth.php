<?php

namespace App\HttpController\Api\Admin;

use App\Model\Admin\AdminModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * 管理员登录控制器
 * @package App\HttpController\Api\Admin
 * @ApiGroup(groupName="后端接口")
 * @ApiGroupDescription("该组为后端页面访问的接口")
 */
class Auth extends AdminBase
{
    protected $whiteList=['login'];

    /**
     * login
     * 登陆,参数验证注解写法
     * @Param(name="account", alias="帐号", required="", lengthMax="20")
     * @Param(name="password", alias="密码", required="", lengthMin="6", lengthMax="16")
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author LZH
     */
    public function login()
    {
        $param = $this->request()->getRequestParam();
        $model = new AdminModel();
        $model->adminAccount = $param['account'];
        $model->adminPassword = md5($param['password']);

        if ($user = $model->login()) {
            $sessionHash = md5(time() . $user->adminId);
            $user->update([
                'adminLastLoginTime' => time(),
                'adminLastLoginIp'   => $this->clientRealIP(),
                'adminSession'       => $sessionHash
            ]);

            $rs = $user->toArray();
            unset($rs['adminPassword']);
            $rs['adminSession'] = $sessionHash;
            // 一个小时有效期
            $this->response()->setCookie('adminSession', $sessionHash, time() + 3600, '/');
            $this->writeJson(Status::CODE_OK, $rs);
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, '', '密码错误');
        }
    }

    /**
     * @Api(name="logout",path="/api/admin/auth/logout")
     * @ApiDescription("用户注销")
     * @ApiRequestExample("curl http://127.0.0.1:9501/api/admin/auth/logout")
     * @ApiAuth(name="登录",required="",description="必须是登录用户才行")
     * @Param(name="adminSession", from={COOKIE}, required="")
     * @ApiSuccess({"code":200,"result":null,"msg":"登出成功"})
     * @ApiFail({"code":401,"result":null,"msg":"尚未登入"})
     * @return bool
     * @author LZH
     */
    public function logout()
    {
        $sessionKey = $this->request()->getRequestParam($this->sessionKey);
        if (empty($sessionKey)) {
            $sessionKey = $this->request()->getCookieParams('adminSession');
        }
        if (empty($sessionKey)) {
            $this->writeJson(Status::CODE_UNAUTHORIZED, '', '尚未登入');
            return false;
        }
        $result = $this->getWho()->logout();
        if ($result) {
            $this->writeJson(Status::CODE_OK, '', "登出成功");
        } else {
            $this->writeJson(Status::CODE_UNAUTHORIZED, '', 'fail');
        }
    }

    /**
     * 获取管理员详细信息
     */
    public function getInfo()
    {
        $this->writeJson(200, $this->getWho()->toArray(), 'success');
    }
}
