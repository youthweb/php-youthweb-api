<?php

namespace Youthweb\Api\Resource;

/**
 * Get the account stats
 *
 * @link https://github.com/youthweb/youthweb-api#account
 */
class Account extends AbstractResource
{
	/**
	 * Get the account stats
	 *
	 * @link https://github.com/youthweb/youthweb-api#stats
	 *
	 * @return array the account stats
	 */
	public function stats()
	{
		$params = array(
			'action' => 'account',
			'cat' => 'stats',
		);

		$stats = $this->get('', $params);

		return array(
			'user_total' => $stats->user_total,
			'user_online' => $stats->user_online,
		);
	}
}
