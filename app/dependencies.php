<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Controllers\GroupsController;
use App\Controllers\MessagesController;
use App\Controllers\UsersController;
use App\Services\DatabaseService;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        PDO::class => function (ContainerInterface $container) {
            $mode = env('APP_ENV') ?? 'production';
            $settings = $container->get(SettingsInterface::class);
            if ($mode == 'testing') {
                $dbData = $settings->get('db_test');
            } else {
                $dbData = $settings->get('db');
            }
            $dsn = 'sqlite:' . $dbData['database'];
            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        },
        DatabaseService::class => function ($container) {
            $pdo = $container->get(PDO::class); // Retrieve PDO instance
            return new DatabaseService($pdo);
        },
        GroupsController::class => function (ContainerInterface $container) {
            $databaseService = $container->get(DatabaseService::class);
            return new GroupsController($databaseService);
        },
        UsersController::class => function (ContainerInterface $container) {
            $databaseService = $container->get(DatabaseService::class);
            return new UsersController($databaseService);
        },
        MessagesController::class => function (ContainerInterface $container) {
            $databaseService = $container->get(DatabaseService::class);
            return new MessagesController($databaseService);
        }
    ]);
};
