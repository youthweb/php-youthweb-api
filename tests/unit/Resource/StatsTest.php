<?php
/*
 * PHP Youthweb API is an object-oriented wrapper for PHP of the Youthweb API.
 * Copyright (C) 2015-2018  Youthweb e.V.  https://youthweb.net
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

use Youthweb\Api\Resource\Stats;
use InvalidArgumentException;

class StatsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function testShowAccountReturnsDocumentInterface()
    {
        $document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('getUnauthorized')
            ->willReturn($document);

        $stats = new Stats($client);

        $response = $stats->show('account');

        $this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
    }

    /**
     * @test
     */
    public function testShowForumReturnsObject()
    {
        $document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('getUnauthorized')
            ->willReturn($document);

        $stats = new Stats($client);

        $response = $stats->show('forum');

        $this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
    }

    /**
     * @test
     */
    public function testShowGroupsReturnsObject()
    {
        $document = $this->createMock('\Art4\JsonApiClient\DocumentInterface');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('getUnauthorized')
            ->willReturn($document);

        $stats = new Stats($client);

        $response = $stats->show('groups');

        $this->assertInstanceOf('\Art4\JsonApiClient\DocumentInterface', $response);
    }

    /**
     * @test
     */
    public function testShowFoobarThrowsException()
    {
        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $stats = new Stats($client);

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The ressource id "foobar" does not exists.');

        $response = $stats->show('foobar');
    }
}
