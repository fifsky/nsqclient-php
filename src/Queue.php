<?php
/**
 * Queue API
 * User: moyo
 * Date: 31/03/2017
 * Time: 3:59 PM
 */

namespace NSQClient;

use NSQClient\Access\Endpoint;
use NSQClient\Connection\Lookupd;
use NSQClient\Connection\Nsqd;
use NSQClient\Connection\Pool;

class Queue
{
    public static function publish(Endpoint $endpoint, $topic, $message)
    {

    }

    /**
     * @param Endpoint $endpoint
     * @param string $topic
     * @param string $channel
     * @param callable $processor
     * @param int $lifecycle
     */
    public static function subscribe(Endpoint $endpoint, $topic, $channel, callable $processor, $lifecycle = 0)
    {
        $routes = Lookupd::getNodes($endpoint, $topic);

        foreach ($routes as $route)
        {
            Pool::register([$route['host'], $route['topic']], function () use ($endpoint, $route, $processor, $lifecycle) {
                return (new Nsqd($endpoint))->setRoute($route)->setLifecycle($lifecycle)->setProcessor($processor);
            })->subscribe($channel);
        }

        Pool::getEvLoop()->run();
    }
}