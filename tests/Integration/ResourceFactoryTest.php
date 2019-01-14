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

namespace Youthweb\Api\Tests\Integration;

use Youthweb\Api\ResourceFactory;

class ResourceFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function testCreateFactory()
    {
        $factory = new ResourceFactory();

        $this->assertInstanceOf('Youthweb\Api\ResourceFactoryInterface', $factory);
    }

    /**
     * @test
     * @dataProvider getResourseClassesProvider
     *
     * @param mixed $resource_name
     * @param mixed $class_name
     */
    public function testCreateResource($resource_name, $class_name)
    {
        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $factory = new ResourceFactory();

        $this->assertInstanceOf(
            $class_name,
            $factory->createResource($resource_name, $client)
        );
    }

    /**
     * Resources DataProvider
     */
    public function getResourseClassesProvider()
    {
        return [
            ['stats', 'Youthweb\Api\Resource\StatsInterface'],
            ['users', 'Youthweb\Api\Resource\UsersInterface'],
        ];
    }

    /**
     * @test
     */
    public function testGetUnknownResourceThrowsException()
    {
        $client = $this->createMock('Youthweb\Api\ClientInterface');

        $factory = new ResourceFactory();

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The resource "foobar" does not exists.');

        $factory->createResource('foobar', $client);
    }
}
