<?php

namespace Youthweb\Api\Exception;

use RuntimeException;

class UnauthorizedException extends RuntimeException
{
	public static function withAuthorizationUrl($url)
	{
		$e = new static('We need an authorization code. Call this url to get one.');
		$e->setUrl($url);

		return $e;
	}

	/**
	 * @var string The auth url
	 */
	private $url = '';

	/**
	 * Set the auth url
	 *
	 * @param string $url The auth url
	 */
	public function setUrl($url)
	{
		$this->url = strval($url);
	}

	/**
	 * Get the auth url
	 *
	 * Redirect the user to this url, e.g. with:
	 * header('Location: '.$e->getUrl());
	 *
	 * @return string The auth url
	 */
	public function getUrl()
	{
		return $this->url;
	}
}
