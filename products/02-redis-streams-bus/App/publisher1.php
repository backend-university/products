<?php

use App\Messages\DaemonLogMessage;
use App\Messages\SecretJsonMessages;
use React\EventLoop\Factory;

require_once __DIR__ . '/../vendor/autoload.php';
/** @var \Psr\Container\ContainerInterface $container */
$container = require_once('config/container.php');
/** @var \Symfony\Component\Messenger\MessageBusInterface $bus */
$bus = $container->get(CONTAINER_REDIS_STREAM_BUS);

$loop = Factory::create();

$loop->addPeriodicTimer(0.1, function () use ($bus) {
    static $i = 1;
    $desc = [
        'someSecret' => uniqid(),
        'someRandomKey' => rand(),
        'sender' => 'publisher1',
    ];
    $message = new SecretJsonMessages('Mes#' . $i, \json_encode($desc), new DateTimeImmutable('now'));
    $bus->dispatch($message);
    \error_log("[- publisher1: SECRET $i sent -] " . date(DATE_ATOM));
    $i++;
});
$loop->addPeriodicTimer(0.5, function () use ($bus) {
    static $i = 1;
    $message = new DaemonLogMessage('Mes#' . $i, new DateTimeImmutable('now'));
    $bus->dispatch($message);
    \error_log("[- publisher1: LOG $i sent -] " . date(DATE_ATOM));
    $i++;
});

$loop->run();
