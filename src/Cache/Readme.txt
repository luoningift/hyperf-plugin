使用帮助

修改config/autoload/cache.php
return [
    'default' => [
        'driver' => Hky\Plugin\Cache\RedisDriver::class,
        'packer' => Hyperf\Utils\Packer\PhpSerializerPacker::class,
        'prefix' => 'wechat:',
        'pool' => 'default', //对应config/autoload/redis.php
    ],
];