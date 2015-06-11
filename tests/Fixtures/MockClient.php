<?php

namespace Youthweb\Api\Fixtures;

use Youthweb\Api\Client;

/**
 * Mock client class
 */
class MockClient extends Client
{
	/**
	 * Return value the mocked runRequest method should return.
	 *
	 * @var mixed
	 */
	public $runRequestReturnValue = null;

	/**
	 * Return value the mocked runRequest method should return.
	 *
	 * @var mixed
	 */
	public $useOriginalGetMethod = false;

	/**
	 * Just return the data from runRequest().
	 *
	 * @param string $path
	 * @param array  $data
	 *
	 * @return array
	 */
	public function get($path, array $data = array())
	{
		if ( $this->useOriginalGetMethod )
		{
			return parent::get($path, $data);
		}

		return $this->runRequest($path, 'GET', $data);
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @param array  $data
	 *
	 * @return string
	 *
	 * @throws \Exception If anything goes wrong on curl request
	 */
	protected function runRequest($path, $method = 'GET', $data = '')
	{
		if ( $this->runRequestReturnValue !== null )
		{
			return $this->runRequestReturnValue;
		}

		return array(
			'path' => $path,
			'method' => $method,
			'data' => $data,
		);
	}
}
