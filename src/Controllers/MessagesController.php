<?php
namespace App\Controllers;

use App\Services\DatabaseService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessagesController
{
    private DatabaseService $dbService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->dbService = $databaseService;
    }

    public function getMessages(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $params = $request->getQueryParams();
        $lastMessageId = $params['last_message'] ?? null;

        if (!$this->dbService->isGroupExisted($id)) {
            return $response->withJson(['success' => false, 'data' => []], 404);
        }

        $messages = $this->dbService->getGroupMessages($id, $lastMessageId);
        return $response->withJson(['success' => true, 'data' => $messages]);
    }

    public function sendMessage(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $userId = $data['user_id'] ?? null;
        $message = $data['message'] ?? null;

        if (!$userId || !$message || !$id) {
            return $response->withJson(['success' => true, 'data' => []], 400);
        }

        if (!$this->dbService->isGroupExisted($id) || !$this->dbService->isUserExisted($userId)) {
            return $response->withJson(['success' => true, 'data' => []], 404);
        }

        $this->dbService->sendMessage($id, $userId, $message);
        return $response->withJson(['success' => true, 'data' => []]);
    }
}
