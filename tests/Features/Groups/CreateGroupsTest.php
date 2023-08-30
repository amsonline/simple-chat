<?php

namespace Tests\Features\Groups;

use App\Services\DatabaseService;
use Slim\App;
use Tests\TestCase;

class CreateGroupsTest extends TestCase
{
    public function testCreateGroup()
    {
        $request = $this->createRequest('POST', '/groups');
        $request = $request->withParsedBody([
            'name' => 'Test Group'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDbHas('groups', [
            'name'  => 'Test Group'
        ]);
    }

    public function testCreateGroupWithSameNameIsOk()
    {
        // First request
        $request = $this->createRequest('POST', '/groups');
        $request = $request->withParsedBody([
            'name' => 'Duplicated group'
        ]);

        $this->app->handle($request);

        // Second request
        $request = $this->createRequest('POST', '/groups');
        $request = $request->withParsedBody([
            'name' => 'Duplicated group'
        ]);

        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertDbHas('groups', [
            'name'  => 'Duplicated Group'
        ], 2);
    }

    public function testEmptyGroupNameShouldRaiseError()
    {
        $request = $this->createRequest('POST', '/groups');

        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
    }
}
