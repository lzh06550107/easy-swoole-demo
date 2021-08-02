<?php

namespace App\HttpController\Api\Common;

use App\Model\Admin\BannerModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * 前端页面banner
 * @package App\HttpController\Api\Common
 * @ApiGroup(groupName="前端接口")
 * @ApiGroupDescription("该组为前端页面访问的接口")
 */
class Banner extends CommonBase
{

    /**
     * @Api(name="getOne",path="/api/common/banner/getOne")
     * @ApiDescription("获取前端显示的某个banner")
     * @ApiRequestExample("curl http://127.0.0.1:9501/api/common/banner/getOne?bannerId=11")
     * @Param(name="bannerId", alias="主键id", required="", integer="")
     * @ApiSuccess({"code":200,"result":{"userId":2,"account":"zyf","username":"es","phone":"xxxx","avatar":null,"createTime":1595837009,"isDelete":null,"deleteTime":null,"user_token":"2-bc429ab40a7a2ebc-1596008468"},"msg":"登录成功"})
     * @ApiFail({"code":400,"result":null,"msg":"字段非法"})
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     * @author LZH
     */
    public function getOne()
    {
        $bannerId = $this->input('bannerId');
        $model = new BannerModel();
        $bean = $model->get($bannerId);
        if ($bean) {
            $this->writeJson(Status::CODE_OK, $bean, "success");
        } else {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], 'fail');
        }
    }

    /**
     * getAll
     * @Param(name="page", alias="页数", optional="", integer="")
     * @Param(name="limit", alias="每页总数", optional="", integer="")
     * @Param(name="keyword", alias="关键字", optional="", lengthMax="32")
     * @author LZH
     */
    public function getAll()
    {
        $param = $this->request()->getRequestParam();
        $page = $param['page'] ?? 1;
        $limit = $param['limit'] ?? 20;
        $model = new BannerModel();
        $data = $model->getAll($page, 1, $param['keyword'] ?? null, $limit);
        $this->writeJson(Status::CODE_OK, $data, 'success');
    }
}
