<?php

use App\Messages\DaemonLogMessage;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;

require_once __DIR__ . '/../vendor/autoload.php';
/** @var \Psr\Container\ContainerInterface $container */
$container = require_once('config/container.php');

$handlers = [
    DaemonLogMessage::class => [
        new HandlerDescriptor(
            function (DaemonLogMessage $m) {
                \error_log(
                    'DaemonLogHandler: message handled: / '
                    . $m->getMessage()
                    . ' / '
                    . $m->getDate()->format(DATE_ATOM)
                );
            },
            [
                'from_transport' => CONTAINER_REDIS_TRANSPORT_LOG,
            ]
        )
    ],
];
$middleware = [];
$middleware[] = new HandleMessageMiddleware(
    new HandlersLocator($handlers),
    false
);
$sendersLocator = new SendersLocator(
    [
        '*' => [CONTAINER_REDIS_TRANSPORT_LOG],
    ],
    $container
);
$middleware[] = new SendMessageMiddleware($sendersLocator);

$bus = new MessageBus($middleware);
$receivers = [
    CONTAINER_REDIS_TRANSPORT_LOG => $container->get(CONTAINER_REDIS_STREAM_RECEIVER_LOG),
];
$w = new \Symfony\Component\Messenger\Worker($receivers, $bus, $container->get(CONTAINER_EVENT_DISPATCHER));
$w->run();
