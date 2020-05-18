<?php
namespace Hky\Plugin\Consul;
use Hyperf\Utils\ApplicationContext;


class ServerBeforeStartCallback {

    /**
     * 注册服务
     */
    public function beforeStart()
    {
        $container = ApplicationContext::getContainer();
        $consul = $container->get(ConsulRegisterService::class);
        echo $consul->add() . PHP_EOL;
    }
}
