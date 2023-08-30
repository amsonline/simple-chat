<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Settings\SettingsInterface;
use App\Controllers\GroupsController;
use App\Controllers\MessagesController;
use App\Controllers\UsersController;
use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Handlers\Strategies\RequestResponseArgs;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->post('/users', UsersController::class . ":getUserId");

    $app->group('/groups', function (Group $group) {
        $group->get('', GroupsController::class . ":getGroups");
        $group->post('', GroupsController::class . ":createGroup");
        $group->post('/{id}/join', GroupsController::class . ":joinGroup");
        $group->get('/{id}/messages', MessagesController::class . ":getMessages");
        $group->post('/{id}/messages', MessagesController::class . ":sendMessage");
    });
};
