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
        'enable' => 1,
        'out_net_card' => '',
    ];

    private $registerIp = '127.0.0.1';

    private $registerPort = 0;

    private $consulId = '';

    private $consulName = '';

    private $consulUrl = '';

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
        //consul服务地址
        $this->consulUrl = explode(';', $this->consulConfig['url']);
        //获取项目名称
        $this->consulName = strval($config->get('app_name'));
        if (!$this->consulName) {
            throw new \InvalidArgumentException('config[config.app_name] is null');
        }
        //注册端口和ip
        $this->registerPort = intval($config->get('server.servers')[0]['port']);
        $clientIp = swoole_get_local_ip();
        $netCard = $this->consulConfig['out_net_card'];
        $this->registerIp = $netCard && isset($clientIp[$netCard]) ? $clientIp[$netCard] : array_pop($clientIp);
        $this->consulId = $this->consulName . '-' . $this->registerIp . ':' . $this->registerPort;
    }

    /**
     * @return boolean
     */
    public function add()
    {

        if (!$this->consulConfig['enable']) {
            return true;
        }

        $registerService = [
            'ID' => $this->consulId,
            'Name' => $this->consulName,
            'Tags' => [
               $this->consulName
            ],
            'Address' => $this->registerIp,
            'Port' => $this->registerPort,
            'Meta' => [
                'version' => '1.0'
            ],
            'EnableTagOverride' => false,
            'Weights' => [
                'Passing' => 10,
                'Warning' => 1
            ],
            'Checks' => [
                [
                    'name' => $this->consulId . '-check',
                    'http' => 'http://' . $this->registerIp . ':' . $this->registerPort . '/health/check',
                    'interval' => "2s",
                    'timeout' => "1s",
                ]
            ]
        ];
        $statusCodes = [];
        foreach ($this->consulUrl as $consulUrl) {
            $agent = new Agent(function () use ($consulUrl) {
                return $this->container->get(ClientFactory::class)->create([
                    'base_uri' => $consulUrl,
                ]);
            });
            $statusCodes[] = $agent->registerService($registerService)->getStatusCode();
        }
        foreach ($statusCodes as $status) {
            if ($status != 200) {
                return false;
            }
        }
        return true;
    }

    /**
     * kill -15 服务停止事件
     * @return boolean
     */
    public function del()
    {

        if (!$this->consulConfig['enable']) {
            return true;
        }
        $statusCodes = [];
        foreach ($this->consulUrl as $consulUrl) {
            $agent = new Agent(function () use ($consulUrl) {
                return $this->container->get(ClientFactory::class)->create([
                    'base_uri' => $consulUrl,
                ]);
            });
            $statusCodes[] = $agent->deregisterService(urlencode($this->consulId))->getStatusCode();
        }
        foreach ($statusCodes as $status) {
            if ($status != 200) {
                return false;
            }
        }
        return true;
    }
}
