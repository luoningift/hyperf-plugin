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
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $container->get(ConfigInterface::class);
        $poolName = 'consul';
        $consulKey = 'hky_plugin.' . $poolName;
        if (!$config->has($consulKey)) {
            throw new \InvalidArgumentException('config[' . $consulKey . '] is not exist!');
        }
        $this->consulConfig = array_replace($this->consulConfig, $config->get($consulKey));
        $projectName = strval($config->get('app_name'));
        if (!$projectName) {
            throw new \InvalidArgumentException('config[config.app_name] is null');
        }
        $this->projectName = $projectName;
        $this->defaultIp = '127.0.0.1:' . $config->get('server.servers')[0]['port'];
        //vim ~/.profile add export HYPERF_HKY_WECHAT_ADDRESS="127.0.0.1:9501"
        $this->defaultIp = $_SERVER['HYPERF_' . str_replace('-', '_', strtoupper($this->projectName)) . '_ADDRESS'] ?? $this->defaultIp;

        $this->consulName = "php-" . strtolower($this->projectName);
        $this->consulId = "php-" . strtolower($this->projectName) . '-' . md5($this->defaultIp);
    }

    public function add() {

        $ipAr = explode(':', $this->defaultIp);
        $registerService = [
            "ID" => $this->consulId ,
            "Name" => $this->consulName,
            "Tags" => ["hky_wechat", "php"],
            "Address" => $ipAr[0],
            "Port" => intval($ipAr[1]),
            "Check" => [
                "http" => "http://" . $this->defaultIp . "/consul/health",
                "DeregisterCriticalServiceAfter" => "90m",
                "interval" => "5s",
                "timeout" => "3s"
            ]
        ];
        $consulUrl = $this->consulConfig['url'];
        $agent = new Agent(function () use ($consulUrl) {
            return $this->container->get(ClientFactory::class)->create([
                'base_uri' => $consulUrl,
            ]);
        });
        return $agent->registerService($registerService)->getBody()->getContents();
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
        $response = $agent->deregisterService($this->consulId);
        $content = $response->getBody()->getContents();
        return $content;
    }
}
