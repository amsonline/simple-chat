<?php

namespace Tests\Features\Groups;

use App\Services\DatabaseService;
use Slim\App;
use Tests\TestCase;

class GetGroupsTest extends TestCase
{
    public function testGetAllGroups()
    {
        $this->db->query("INSERT INTO groups (name) VALUES ('Group 1'), ('Group 2'), ('Group 3')");

        $request = $this->createRequest('GET', '/groups');
        $request = $request->withQueryParams([
            'user_id' => 1
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $this->assertCount(3, $responseData['data']);
    }

    public function testShouldViewJoinedGroupsCorrectly()
    {
        $this->db->query("INSERT INTO groups (id, name) VALUES (1000, 'Group 1'), (1001, 'Group 2'), (1002, 'Group 3')");
        $this->db->query("INSERT INTO group_members (group_id, user_id) VALUES (1000, 1)");

        $request = $this->createRequest('GET', '/groups');
        $request = $request->withQueryParams([
            'user_id' => 1
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $this->assertHasJoinedGroupId($responseData, 1000);
    }

    public function testEmptyUserIdShouldRaiseError()
    {
        $request = $this->createRequest('GET', '/groups');

        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
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
