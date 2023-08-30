<?php

declare(strict_types=1);

namespace Tests\Features;

use App\Services\DatabaseService;
use Slim\App;
use Tests\TestCase;

class UsersTest extends TestCase
{
    public function testNewUserIdGetsRegistered()
    {
        $request = $this->createRequest('POST', '/users');
        $request = $request->withParsedBody([
            'name'  => 'Test user'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);

        $this->assertDbHas('users', ['name' => 'Test user']);
    }

    public function testEmptyUserNameShouldResponseInError()
    {
        $request = $this->createRequest('POST', '/users');
        $request = $request->withParsedBody([
            'name'  => ''
        ]);

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
}
