<?php

namespace Hky\Plugin\Consul;

use Hyperf\Consul\Agent;
use Hyperf\Consul\Health;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Guzzle\ClientFactory;

/**
 * register self to consul
 * Class ConsulRegisterService
 * @package Hky\Plugin\Consul
 */
class ConsulRegisterService
{

    private $container;

    private $consulConfig = [
        'url' => 'http://127.0.0.1:8500',
    ];

    private $projectName = '';

    private $defaultIp = '127.0.0.1:9501';

    private $consulId = '';

    private $consulName = '';

    /**
     * @param $container
     */
    public function __construct(ContainerInterface $container, $poolName)
    {
        $this->container = $container;
        $config = $container->get(ConfigInterface::class);
        $consulKey = 'hky_plugin.' . $poolName;
        if (!$config->has($consulKey)) {
            throw new \InvalidArgumentException('config[' . $consulKey . '] is not exist!');
        }
        $this->consulConfig = array_replace($this->consulConfig, $config->get($consulKey));
        $projectName = strval($config->get('config.app_name'));
        if (!$projectName) {
            throw new \InvalidArgumentException('config[config.app_name] is null');
        }
        $this->projectName = $projectName;
        $this->defaultIp = '127.0.0.1:' . $config->get('server.servers')[0]['port'];
        //vim ~/.profile add export HYPERF_HKY_WECHAT_ADDRESS="127.0.0.1:9501"
        $this->defaultIp = $_SERVER['HYPERF_' . strtoupper($this->projectName) . '_ADDRESS'] ?? $this->defaultIp;

        $this->consulName = "php." . strtolower($this->projectName);
        $this->consulId = $this->projectName . '_' . md5($this->defaultIp);
    }

    // 服务注册
    public function add()
    {

        var_dump($this->addServer());
        $object = $this;
        \Swoole\Timer::tick(10000, function() use ($object) {
            $response = $object->addServer();
            var_dump($response);
        });
    }

    public function addServer() {

        $ipAr = explode(':', $this->defaultIp);
        $registerService = [
            "ID" => "php." . $this->consulId ,
            "Name" => "php." . $this->consulName,
            "Tags" => ["primary"],
            "Address" => $ipAr[0],
            "Port" => $ipAr[1],
            "Check" => [
                "id" => "api",
                "name" => "check consul health on " . $this->defaultIp,
                "http" => "http://" . $this->defaultIp . "/consul/health",
                "interval" => "1s",
                "timeout" => "1s"
            ]
        ];
        $consulUrl = $this->consulConfig['url'];
        $agent = new Agent(function () use ($consulUrl) {
            return $this->container->get(ClientFactory::class)->create([
                'base_uri' => $consulUrl,
            ]);
        });
        return $agent->registerService($registerService);
    }

    /**
     * kill -15 服务停止事件
     * @return \Hyperf\Consul\ConsulResponse
     */
    public function del() {

        $consulUrl = $this->consulConfig['url'];
        $agent = new Agent(function () use ($consulUrl) {
            return $this->container->get(ClientFactory::class)->create([
                'base_uri' => $consulUrl,
            ]);
        });
        return $agent->deregisterService($this->consulId);
    }
}
