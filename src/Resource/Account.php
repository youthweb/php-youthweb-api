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
		$stats = $this->get('/account/stats');

		return array(
			'user_total' => $stats->user_total,
			'user_online' => $stats->user_online,
		);
	}
}
