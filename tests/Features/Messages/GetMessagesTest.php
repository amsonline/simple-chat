<?php

namespace Tests\Features\Messages;

use App\Services\DatabaseService;
use Slim\App;
use Tests\TestCase;

class GetMessagesTest extends TestCase
{
    public function testGetMessages()
    {
        $this->db->query("INSERT INTO groups (id, name) VALUES (3000, 'Group Y')");
        $this->db->query("INSERT INTO users (id, name) VALUES (2000, 'User Y')");
        $this->db->query("INSERT INTO messages (user_id, group_id, content) VALUES (2000, 3000, 'Message 1'), (2000, 3000, 'Message 2'), (2000, 3000, 'Message 3')");

        $request = $this->createRequest('GET', '/groups/3000/messages');

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $this->assertCount(3, $responseData['data']);
    }

    public function testGetMessagesWithLastMessage()
    {
        $this->db->query("INSERT INTO groups (id, name) VALUES (3000, 'Group Y')");
        $this->db->query("INSERT INTO users (id, name) VALUES (2000, 'User Y')");
        $this->db->query("INSERT INTO messages (id, user_id, group_id, content) VALUES (9000, 2000, 3000, 'Message 1'), (9001, 2000, 3000, 'Message 2'), (9002, 2000, 3000, 'Message 3')");

        $request = $this->createRequest('GET', '/groups/3000/messages');
        $request = $request->withQueryParams([
            'last_message' => 9001
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        // There is only one single message with id > 9001
        $this->assertCount(1, $responseData['data']);
    }

    public function testNonExistentGroupShouldRaiseError()
    {
        $request = $this->createRequest('GET', '/groups/9999/messages');

        $response = $this->app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testExistingUserShouldReturnExistingId()
    {
        $request = $this->createRequest('POST', '/users');

        // The userId is being created here
        $userId = $this->db->getUserId('Test user');

        $request = $request->withParsedBody([
            'name'  => 'Test user'
        ]);

        $response = $this->app->handle($request);

        $responseData = json_decode((string)$response->getBody(), true);
        $newUserId = $responseData['data']['user_id'];

        $this->assertEquals($newUserId, $userId);
    }

    private function assertHasJoinedGroupId($data, $groupId)
    {
        $found = false;
        foreach ($data['data'] as $group) {
            if ($group['id'] == $groupId && $group['isJoined'] == 1) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
