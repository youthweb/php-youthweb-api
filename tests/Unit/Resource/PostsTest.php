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

use Youthweb\Api\Resource\Posts;
use InvalidArgumentException;

class PostsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test show()
     */
    public function testShowReturnsDocumentInterface()
    {
        $document = $this->createMock('\Art4\JsonApiClient\Accessable');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('get')
            ->willReturn($document);

        $posts = new Posts($client);

        $postId = 'af25fe45-a796-449c-8a42-44e647e2454f';

        $this->assertSame($document, $posts->show($postId));
    }

    /**
     * @test show() with Exception
     */
    public function testShowThrowsException()
    {
        $exception = new \Exception('Resource not found', 404);

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->any())
            ->method('get')
            ->will($this->throwException($exception));

        $posts = new Posts($client);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Resource not found');

        $response = $posts->show('invalid_post_id');
    }

    /**
     * @test showAuthor()
     */
    public function testShowAuthorReturnsDocumentInterface()
    {
        $document = $this->createMock('\Art4\JsonApiClient\Accessable');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('get')
            ->willReturn($document);

        $posts = new Posts($client);

        $postId = 'af25fe45-a796-449c-8a42-44e647e2454f';

        $this->assertSame($document, $posts->showAuthor($postId));
    }

    /**
     * @test showAuthor() with Exception
     */
    public function testShowAuthorThrowsException()
    {
        $exception = new \Exception('Resource not found', 404);

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->any())
            ->method('get')
            ->will($this->throwException($exception));

        $posts = new Posts($client);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Resource not found');

        $response = $posts->showAuthor('invalid_post_id');
    }

    /**
     * @test showAuthorRelationship()
     */
    public function testShowAuthorRelationshipReturnsDocumentInterface()
    {
        $document = $this->createMock('\Art4\JsonApiClient\Accessable');

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->once())
            ->method('get')
            ->willReturn($document);

        $posts = new Posts($client);

        $postId = 'af25fe45-a796-449c-8a42-44e647e2454f';

        $this->assertSame($document, $posts->showAuthorRelationship($postId));
    }

    /**
     * @test showAuthorRelationship() with Exception
     */
    public function testShowAuthorRelationshipThrowsException()
    {
        $exception = new \Exception('Resource not found', 404);

        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $client->expects($this->any())
            ->method('get')
            ->will($this->throwException($exception));

        $posts = new Posts($client);

        $this->expectException('Exception');
        $this->expectExceptionMessage('Resource not found');

        $response = $posts->showAuthorRelationship('invalid_post_id');
    }
}
