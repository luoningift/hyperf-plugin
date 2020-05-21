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
namespace Hky\Plugin;

use Hky\Plugin\Consul\ConsulOnAfterWorkerStartListener;
use Hky\Plugin\Consul\ConsulOnBeforeMainWorkerStartListener;
use Hky\Plugin\Consul\ConsulOnShutdownListener;
use Hky\Plugin\Consul\ConsulOnStartListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                ConsulOnShutdownListener::class,
                ConsulOnStartListener::class,
                ConsulOnBeforeMainWorkerStartListener::class,
                ConsulOnAfterWorkerStartListener::class,
            ],
            'dependencies' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
                'ignore_annotations' => [
                    'mixin',
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of mongo client.',
                    'source' => __DIR__ . '/publish/hky_plugin.php',
                    'destination' => BASE_PATH . '/config/autoload/hky_plugin.php',
                ],
            ],
        ];
    }
}
