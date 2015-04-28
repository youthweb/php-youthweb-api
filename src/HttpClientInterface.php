<?php

namespace Youthweb\Api;

/**
 * Http client interface
 */
interface HttpClientInterface
{
	/**
	* Send a GET request
		*
	* @param string|array $url     URL
	* @param array        $options Array of request options to apply.
	 *
	* @return mixed
	*/
	public function get($url = null, array $options = array());
}
