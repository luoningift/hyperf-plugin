使用帮助
heyperf中执行

php bin/hyperf.php vendor:publish hky/plugin

修改config/autoload/hky_plugin.php中url为consul服务的地址

'callbacks' => [
        SwooleEvent::ON_BEFORE_START => [Hky\Plugin\Consul\ServerBeforeStartCallback::class, 'beforeStart'],
        SwooleEvent::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        SwooleEvent::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        SwooleEvent::ON_SHUTDOWN     => [Hky\Plugin\Consul\ServerShutDownCallback::class, 'beforeShutDown']
],

supervisor 或者其他进程管理工具 发信号kill -15 到主进程

使用环境变量设置ip和端口号
编辑~/.profile 增加 export HYPERF_HKY_WECHAT_ADDRESS="192.168.10.10:9501"
HKY_WECHAT 为 config/config.php的app_name 替换-为_