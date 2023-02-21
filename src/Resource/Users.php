<?php

declare(strict_types=1);
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

namespace Youthweb\Api\Resource;

/**
 * Users
 *
 * @see https://developer.youthweb.net/api_endpoint_users.html
 */
final class Users implements UsersInterface
{
    use ClientTrait;

    /**
     * Get a user
     *
     * @see https://developer.youthweb.net/api_endpoint_users.html
     *
     * @param string $id
     *
     * @return array the user data
     */
    public function show(string $id)
    {
        return $this->client->get('/users/' . strval($id));
    }

    /**
     * Get the authorized user
     *
     * @see https://developer.youthweb.net/api_endpoint_me.html
     *
     * @return \Art4\JsonApiClient\Accessable
     */
    public function showMe()
    {
        return $this->client->get('/me');
    }

    /**
     * Get the posts of a user
     *
     * @see https://developer.youthweb.net/api_endpoint_object_posts.html#read
     *
     * @param string $id
     *
     * @return array the posts data
     */
    public function showPosts(string $id)
    {
        return $this->client->get('/users/' . strval($id));
    }
}
