<?php
/*
 * PHP Youthweb API is an object-oriented wrapper for PHP of the Youthweb API.
 * Copyright (C) 2015-2019  Youthweb e.V.  https://youthweb.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Youthweb\Api\Tests\Unit\Resource;

use Youthweb\Api\Resource\Users;
use InvalidArgumentException;

class UsersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test show()
     */
    public function testShowUserReturnsDocumentInterface()
    {
        $document = $this->createMock('\Art4\JsonApiClient\Accessable');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('get')
            ->willReturn($document);

        $users = new Users($client);

        $user_id = 123456;

        $this->assertSame($document, $users->show($user_id));
    }

    /**
     * @test show() with Exception
     */
    public function testShowFoobarThrowsException()
    {
        $exception = new \Exception('Resource not found', 404);

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->any())
            ->method('get')
            ->will($this->throwException($exception));

        $users = new Users($client);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Resource not found');

        $response = $users->show('invalid_user_id');
    }

    /**
     * @test showMe()
     */
    public function testShowMeReturnsDocumentInterface()
    {
        $document = $this->createMock('\Art4\JsonApiClient\Accessable');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('get')
            ->willReturn($document);

        $users = new Users($client);

        $this->assertSame($document, $users->showMe());
    }
}
