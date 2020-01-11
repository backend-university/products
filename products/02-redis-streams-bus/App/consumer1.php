<?php

use App\Messages\SecretJsonMessages;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';
/** @var \Psr\Container\ContainerInterface $container */
$container = require_once('config/container.php');
/** @var \Symfony\Component\Messenger\MessageBusInterface $busLog */
$busLog = $container->get(CONTAINER_REDIS_STREAM_BUS);

$handlers = [
    SecretJsonMessages::class => [
        new HandlerDescriptor(
            function (SecretJsonMessages $m) use ($busLog) {
                //sending a message through BUS_LOG
                $m = new \App\Messages\DaemonLogMessage(
                    'SecretJsonMessages: message handled: '
                    . $m->getTitle()
                    . ' '
                    . \json_decode($m->getDescription())->someSecret
                    . ' '
                    . $m->getDate()->format(DATE_ATOM),
                    new DateTimeImmutable()
                );
                $busLog->dispatch($m);
            },
            [
                'from_transport' => CONTAINER_REDIS_TRANSPORT_SECRET,
            ]
        )
    ],
];
$middleware = [];
$middleware[] = new HandleMessageMiddleware(
    new HandlersLocator($handlers),
    false
);

$bus = new MessageBus($middleware);
$receivers = [
    CONTAINER_REDIS_TRANSPORT_SECRET => $container->get(CONTAINER_REDIS_STREAM_RECEIVER_SECRET),
];
$w = new \Symfony\Component\Messenger\Worker($receivers, $bus, $container->get(CONTAINER_EVENT_DISPATCHER));
$w->run();
