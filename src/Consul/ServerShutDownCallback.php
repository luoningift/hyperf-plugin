<?php

namespace Hky\Plugin\Consul;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;

class ServerShutDownCallback
{
    /**
     * 从注册中心删除当前服务
     */
    public function beforeShutDown()
    {

        $container = ApplicationContext::getContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        try {
            $registerServer = $container->get(ConsulRegisterService::class);
            if (!$registerServer->del()) {
                $logger->error('deregister to consul failed');
            }
        } catch (\Exception $throwable) {
            $logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $logger->error($throwable->getTraceAsString());
        }
    }
}