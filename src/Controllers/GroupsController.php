<?php
namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GroupsController
{
    private DatabaseService $dbService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->dbService = $databaseService;
    }

    public function createGroup(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        if (!isset($data['name']) || $data['name'] == '') {
            return $response->withJson(['success' => false, 'data' => []], 400);
        }
        $name = $data['name'];

        $this->dbService->createGroup($name);

        $data = ['success' => true, 'data' => []];
        return $response->withJson($data);
    }

    public function getGroups(Request $request, Response $response, $args)
    {
        $params = $request->getQueryParams();

        if (!isset($params['user_id'])) {
            $data = ['success' => false, 'data' => []];
            return $response->withJson($data, 400);
        }
        $userId = $params['user_id'];
        $groups = $this->dbService->getGroups($userId);

        $data = ['success' => true, 'data' => $groups];

        return $response->withJson($data);
    }

    public function joinGroup(Request $request, Response $response, $args)
    {
        $groupId = $args['id'];
        $data = $request->getParsedBody();
        $userId = $data['user_id'];

        if (!$this->dbService->isGroupExisted($groupId) || !$this->dbService->isUserExisted($userId)) {
            return $response->withJson(['success' => false, 'data' => []], 400);
        }

        if ($this->dbService->isJoinedGroup($userId, $groupId)) {
            return $response->withJson(['success' => false, 'data' => []], 409);
        }

        $this->dbService->joinGroup($userId, $groupId);

        $data = ['success' => true, 'data' => []];
        return $response->withJson($data);
    }
}
