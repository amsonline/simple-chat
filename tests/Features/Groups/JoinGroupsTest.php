<?php

namespace Tests\Features\Groups;

use App\Services\DatabaseService;
use Slim\App;
use Tests\TestCase;

class JoinGroupsTest extends TestCase
{
    public function testJoinGroup()
    {
        $this->db->query("INSERT INTO groups (id, name) VALUES (2000, 'Group X')");
        $this->db->query("INSERT INTO users (id, name) VALUES (1000, 'User X')");
        $request = $this->createRequest('POST', '/groups/2000/join');
        $request = $request->withParsedBody([
            'user_id' => 1000
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDbHas('group_members', [
            'user_id'  => 1000,
            'group_id' => 2000
        ]);
    }

    public function testJoinTwiceShouldRaiseError()
    {
        $this->db->query("INSERT INTO groups (id, name) VALUES (2000, 'Group X')");
        $this->db->query("INSERT INTO users (id, name) VALUES (1000, 'User X')");
        // First request
        $request = $this->createRequest('POST', '/groups/2000/join');
        $request = $request->withParsedBody([
            'user_id' => 1000
        ]);

        $this->app->handle($request);

        // Second request
        $request = $this->createRequest('POST', '/groups/2000/join');
        $request = $request->withParsedBody([
            'user_id' => 1000
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(409, $response->getStatusCode());
    }

    public function testJoiningInvalidGroupIsNotOk()
    {
        $request = $this->createRequest('POST', '/groups/9999/join');
        $request = $request->withParsedBody([
            'user_id' => 1000
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testJoiningWithInvalidUserIsNotOk()
    {
        $request = $this->createRequest('POST', '/groups/2000/join');
        $request = $request->withParsedBody([
            'user_id' => 9999
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }
}
