<?php
namespace Hky\Plugin\Consul;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;


class ServerBeforeStartCallback {

    /**
     * 注册服务
     */
    public function beforeStart()
    {
        $container = ApplicationContext::getContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        try {
            $consul = $container->get(ConsulRegisterService::class);
            if (!$consul->add()) {
                $logger->error('register to consul failed');
            }
        } catch (\Exception $throwable) {
            $logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $logger->error($throwable->getTraceAsString());
        }
    }
}
