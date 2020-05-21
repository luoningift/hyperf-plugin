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
use Hyperf\Framework\Event\OnManagerStop;
use Hyperf\Framework\Event\OnShutdown;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

class ConsulOnAfterWorkerStartListener implements ListenerInterface
{

    public function listen(): array
    {
        return [
            AfterWorkerStart::class,
        ];
    }

    /**
     * @param $event
     */
    public function process(object $event)
    {
        $container = ApplicationContext::getContainer();
        $container->get(StdoutLoggerInterface::class)->info('consul: atomic incr one success!');
        $container->get(ConsulRegisterAtomic::class)->add();
    }
}
