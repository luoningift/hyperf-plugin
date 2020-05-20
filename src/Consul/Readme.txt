使用帮助
heyperf中执行

php bin/hyperf.php vendor:publish hky/plugin

修改config/autoload/hky_plugin.php中url为consul服务的地址 多个地址以;分割
例如 'url' => 'http://127.0.0.1:8500;http://127.0.0.1:9800';

callbacks 增加一下内容
'callbacks' => [
        SwooleEvent::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown'],
]

