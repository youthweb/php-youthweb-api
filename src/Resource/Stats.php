<?php

namespace Youthweb\Api\Resource;

/**
 * Get the stats
 *
 * @link https://github.com/youthweb/youthweb-api#stats
 */
class Stats extends AbstractResource
{
	/**
	 * Get the stats
	 *
	 * @link https://github.com/youthweb/youthweb-api#stats
	 *
	 * @param string $id
	 * @return array the stats
	 */
	public function show($id)
	{
		$ids = array(
			'account' => 'account',
			'forum'   => 'forum',
			'groups'  => 'groups',
		);

		if ( ! isset($ids[$id]) )
		{
			throw new \InvalidArgumentException('The ressource id "' . $id . '" does not exists.');
		}

		return $this->get('/stats/' . $ids[$id]);
	}
}
