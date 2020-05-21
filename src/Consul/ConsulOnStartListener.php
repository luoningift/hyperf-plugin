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
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

class ConsulOnStartListener implements ListenerInterface
{
    
    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    /**
     * @param BeforeProcessHandle $event
     */
    public function process(object $event)
    {
        go(function() {
            $container = ApplicationContext::getContainer();
            $logger = $container->get(StdoutLoggerInterface::class);
            try {
                while (!$container->get(ConsulRegisterAtomic::class)->isReady()) {
                    $logger->info('consul: wait worker to ready !');
                    sleep(1);
                }
                $logger->info('consul: begin to register !');
                $consul = $container->get(ConsulRegisterService::class);
                if (!$consul->add()) {
                    $logger->error('consul: register to consul failed');
                }
            } catch (\Exception $throwable) {
                $logger->error(sprintf('%s[%s] in %s', 'consul: ' . $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
                $logger->error('consul: ' . $throwable->getTraceAsString());
            }    
        });
    }
}