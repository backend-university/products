<?php

use App\Decorators\Base64SerializerDecorator;
use DI\Container;
use DI\Definition\Source\DefinitionArray;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\RedisExt\Connection;
use Symfony\Component\Messenger\Transport\RedisExt\RedisReceiver;
use Symfony\Component\Messenger\Transport\RedisExt\RedisTransport;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;

define('ENV_PROD', false);

const CONTAINER_SERIALIZER = 'serializer';
const CONTAINER_REDIS_TRANSPORT_SECRET = 'containerRedisTransportSecret';
const CONTAINER_REDIS_TRANSPORT_LOG = 'containerRedisTransportLog';
const CONTAINER_REDIS_STREAM_RECEIVER_SECRET = 'containerRedisReceiverSecret';
const CONTAINER_REDIS_STREAM_RECEIVER_LOG = 'containerRedisReceiverLog';
const CONTAINER_REDIS_STREAM_CONNECTION_SECRET = 'containerRedisStreamConnectionSecret';
const CONTAINER_REDIS_STREAM_CONNECTION_LOG = 'containerRedisStreamConnectionLog';
const CONTAINER_REDIS_STREAM_BUS = 'containerRedisStreamBus';
const CONTAINER_EVENT_DISPATCHER = 'containerEventDispatcher';

return new Container(
    new DefinitionArray([
        CONTAINER_SERIALIZER => function (ContainerInterface $c) {
            $serializer = new PhpSerializer();

            return new Base64SerializerDecorator($serializer);
        },
        CONTAINER_REDIS_TRANSPORT_SECRET => function (ContainerInterface $c) {
            return new RedisTransport(
                $c->get(CONTAINER_REDIS_STREAM_CONNECTION_SECRET),
                $c->get(CONTAINER_SERIALIZER))
            ;
        },
        CONTAINER_REDIS_TRANSPORT_LOG => function (ContainerInterface $c) {
            return new RedisTransport(
                $c->get(CONTAINER_REDIS_STREAM_CONNECTION_LOG),
                $c->get(CONTAINER_SERIALIZER))
            ;
        },
        CONTAINER_REDIS_STREAM_RECEIVER_SECRET => function (ContainerInterface $c) {
            return new RedisReceiver(
                $c->get(CONTAINER_REDIS_STREAM_CONNECTION_SECRET),
                $c->get(CONTAINER_SERIALIZER)
            );
        },
        CONTAINER_REDIS_STREAM_RECEIVER_LOG => function (ContainerInterface $c) {
            return new RedisReceiver(
                $c->get(CONTAINER_REDIS_STREAM_CONNECTION_LOG),
                $c->get(CONTAINER_SERIALIZER)
            );
        },
        CONTAINER_REDIS_STREAM_BUS => function (ContainerInterface $c) {
            $sendersLocator = new SendersLocator([
                \App\Messages\SecretJsonMessages::class => [CONTAINER_REDIS_TRANSPORT_SECRET],
                \App\Messages\DaemonLogMessage::class => [CONTAINER_REDIS_TRANSPORT_LOG],
            ], $c);
            $middleware[] = new SendMessageMiddleware($sendersLocator);

            return new MessageBus($middleware);
        },
        CONTAINER_REDIS_STREAM_CONNECTION_SECRET => function (ContainerInterface $c) {
            $host = 'bu-02-redis';
            $port = 6379;
            $dsn = "redis://$host:$port";
            $options = [
                'stream' => 'secret',
                'group' => 'default',
                'consumer' => 'default',
            ];

            return Connection::fromDsn($dsn, $options);
        },
        CONTAINER_REDIS_STREAM_CONNECTION_LOG => function (ContainerInterface $c) {
            $host = 'bu-02-redis';
            $port = 6379;
            $dsn = "redis://$host:$port";
            $options = [
                'stream' => 'log',
                'group' => 'default',
                'consumer' => 'default',
            ];

            return Connection::fromDsn($dsn, $options);
        },
        CONTAINER_EVENT_DISPATCHER => function (ContainerInterface $c) {
            $dispatcher = new EventDispatcher();
            $dispatcher->addListener(
                WorkerMessageFailedEvent::class,
                function (WorkerMessageFailedEvent $e) {
                    $message = $e->getThrowable()->getMessage();
                    \error_log("EventDispatcher [error]: $message");
                }
            );
            $dispatcher->addListener(
                WorkerMessageHandledEvent::class,
                function (WorkerMessageFailedEvent $e) {
                    $arr = [
                        $e->getReceiverName(),
                        \get_class($e->getEnvelope()->getMessage()),
                    ];
                    \error_log('EventDispatcher [info]: ' . \implode($arr));
                }
            );
        },
    ])
);
