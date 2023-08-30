<?php

namespace Tests\Features\Messages;

use App\Services\DatabaseService;
use Slim\App;
use Tests\TestCase;

class SendMessagesTest extends TestCase
{
    public function testSendMessage()
    {
        $this->db->query("INSERT INTO groups (id, name) VALUES (4000, 'Group Z')");
        $this->db->query("INSERT INTO users (id, name) VALUES (3000, 'User Z')");

        $request = $this->createRequest('POST', '/groups/4000/messages');
        $request = $request->withParsedBody([
            'user_id' => 3000,
            'message' => 'Hello, world!'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDbHas('messages', [
            'user_id' => 3000,
            'group_id' => 4000,
            'content' => 'Hello, world!'
        ]);
    }

    public function testEmptyMessageShouldRaiseError()
    {
        $request = $this->createRequest('POST', '/groups/4000/messages');
        $request = $request->withParsedBody([
            'user_id' => 3000,
            'message' => ''
        ]);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testNoUserIdShouldRaiseError()
    {
        $request = $this->createRequest('POST', '/groups/4000/messages');
        $request = $request->withParsedBody([
            'message' => 'Hi'
        ]);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testNonExistentGroupShouldRaiseError()
    {
        $request = $this->createRequest('POST', '/groups/9999/messages');
        $request = $request->withParsedBody([
            'user_id' => 3000,
            'message' => 'Hello, world!'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(404, $response->getStatusCode());
    }
}
