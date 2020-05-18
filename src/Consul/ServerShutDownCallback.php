<?php

namespace Hky\Plugin\Consul;

use Hyperf\Utils\ApplicationContext;

class ServerShutDownCallback
{
    /**
     * 从注册中心删除当前服务
     */
    public function beforeShutDown()
    {
        $registerServer = ApplicationContext::getContainer()->get(ConsulRegisterService::class);
        var_dump($registerServer->del());
    }
}