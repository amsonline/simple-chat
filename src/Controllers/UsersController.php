<?php
namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsersController
{
    private DatabaseService $dbService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->dbService = $databaseService;
    }

    public function getUserId(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $name = $data['name'];
        if (!$name) {
            return $response->withJson(['success' => false, 'data' => [
                'error' => 'EMPTY_USERNAME'
            ]], 400);
        }

        $userId = $this->dbService->getUserId($name);
        return $response->withJson(['success' => true, 'data' => [
            'user_id' => $userId
        ]]);
    }
}
