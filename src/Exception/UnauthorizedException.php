<?php

namespace Youthweb\Api\Exception;

use RuntimeException;

class UnauthorizedException extends RuntimeException
{
	public static function withAuthorizationUrl($url, $state = '')
	{
		$e = new static('We need an authorization code. Call this url to get one.');

		$e->setUrl($url);
		$e->setState($state);

		return $e;
	}

	/**
	 * @var string The auth url
	 */
	private $url = '';

	/**
	 * @var string The state
	 */
	private $state = '';

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

	/**
	 * Set the state
	 *
	 * @param string $state The state
	 */
	public function setState($state)
	{
		$this->state = strval($state);
	}

	/**
	 * Get the state
	 *
	 * Compare this state with the state in redirect_url
	 *
	 * @return string The state
	 */
	public function getState()
	{
		return $this->state;
	}
}
