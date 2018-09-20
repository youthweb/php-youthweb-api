<?php

namespace Youthweb\Api\Resource;

/**
 * Users
 *
 * @see docs.youthweb.apiary.io/#reference/users
 */
final class Users implements UsersInterface
{
    use ClientTrait;

    /**
     * Get a user
     *
     * @see docs.youthweb.apiary.io/#reference/users
     *
     * @param string $id
     *
     * @return array the stats
     */
    public function show($id)
    {
        return $this->client->get('/users/' . $id);
    }

    /**
     * Get the resource owner
     *
     * @see http://docs.youthweb.apiary.io/#reference/users/me
     *
     * @return Art4\JsonApiClient\Document
     */
    public function showMe()
    {
        return $this->client->get('/me');
    }
}
