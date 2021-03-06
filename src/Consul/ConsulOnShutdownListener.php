<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hky\Plugin\Consul;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnManagerStop;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

class ConsulOnShutdownListener implements ListenerInterface
{
   
    public function listen(): array
    {
        return [
            OnManagerStop::class,
        ];
    }

    /**
     * @param $event
     */
    public function process(object $event)
    {
        $container = ApplicationContext::getContainer();
        $logger = $container->get(StdoutLoggerInterface::class);
        try {
            $registerServer = $container->get(ConsulRegisterService::class);
            if (!$registerServer->del()) {
                $logger->error('consul: deregister to consul failed');
            }
        } catch (\Exception $throwable) {
            $logger->error(sprintf('%s[%s] in %s', 'consul: ' . $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
            $logger->error('consul: ' . $throwable->getTraceAsString());
        }
    }
}