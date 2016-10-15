<?php

namespace Youthweb\Api\Resource;

/**
 * Users
 *
 * @link docs.youthweb.apiary.io/#reference/users
 */
final class Users extends AbstractResource
{
	/**
	 * Get a user
	 *
	 * @link docs.youthweb.apiary.io/#reference/users
	 *
	 * @param string $id
	 * @return array the stats
	 */
	public function show($id)
	{
		return $this->get('/users/' . $id);
	}
}
