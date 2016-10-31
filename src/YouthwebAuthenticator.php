<?php

namespace Youthweb\Api;

use Youthweb\OAuth2\Client\Provider\Youthweb as Oauth2Provider;

/**
 * Interface for authenticator
 */
class YouthwebAuthenticator implements AuthenticatorInterface
{
	/**
	 * @var string
	 */
	private $api_domain = 'https://api.youthweb.net';

	/**
	 * @var string
	 */
	private $auth_domain = 'https://youthweb.net';

	/**
	 * @var string
	 */
	private $client_id;

	/**
	 * @var string
	 */
	private $client_secret;

	/**
	 * @var string
	 */
	private $redirect_url = '';

	/**
	 * Constructs the Authenticator
	 *
	 * @param array $options An array of options to set on the client.
	 *     Options include `api_domain`, `auth_domain`, `client_id`,
	 *     `client_secret` and `redirect_url`.
	 * @param array $collaborators An array of collaborators that may be used to
	 *     override this provider's default behavior. Collaborators include
	 *     `oauth2_provider`.
	 */
	public function __construct(array $options = [], array $collaborators = [])
	{
		$allowed_options = [
			'api_domain',
			'auth_domain',
			'client_id',
			'client_secret',
			'redirect_url',
		];

		foreach ($options as $option => $value)
		{
			if ( in_array($option, $allowed_options) )
			{
				$value = strval($value);

				$this->{$option} = $value;
			}
		}

		if ( empty($collaborators['oauth2_provider']) )
		{
			$collaborators['oauth2_provider'] = new Oauth2Provider([
				'clientId'     => $this->client_id,
				'clientSecret' => $this->client_secret,
				'redirectUri'  => $this->redirect_url,
				'apiDomain'    => $this->api_domain,
				'domain'       => $this->auth_domain,
			]);
		}

		$this->setOauth2Provider($collaborators['oauth2_provider']);
	}

	/**
	 * get the authorization url
	 *
	 * @param  array $options
	 * @return string
	 */
	public function getAuthorizationUrl(array $options = [])
	{
		return $this->getOauth2Provider()->getAuthorizationUrl($options);
	}

	/**
	 * get a random state
	 *
	 * @return string
	 */
	public function getState()
	{
		return $this->getOauth2Provider()->getState();
	}

	/**
	 * Get an access token
	 *
	 * @param string $grant e.g. `authorization_code`
	 * @param array $params for authorization code:
	 * [
	 *     'code' => 'authorization_code_from_callback_url...',
	 *     'state' => 'state_from_callback_url_for_csrf_protection',
	 * ]
	 *
	 * @throws InvalidArgumentException If a wrong state or grant was set
	 *
	 * @return void
	 */
	public function getAccessToken($grant, array $params = [])
	{
		$allowed_grants = [
			'authorization_code',
		];

		if ( ! in_array($grant, $allowed_grants) )
		{
			throw new \InvalidArgumentException('Unsupported grant "'.strval($grant).'"');
		}

		return $this->getOauth2Provider()->getAccessToken($grant, $params);
	}

	/**
	 * Set a oauth2 provider
	 *
	 * @param Youthweb\OAuth2\Client\Provider\Youthweb $oauth2_provider the oauth2 provider
	 * @return self
	 */
	private function setOauth2Provider(Oauth2Provider $oauth2_provider)
	{
		$this->oauth2_provider = $oauth2_provider;

		return $this;
	}

	/**
	 * Get the oauth2 provider
	 *
	 * @return Youthweb\OAuth2\Client\Provider\Youthweb the oauth2 provider
	 */
	private function getOauth2Provider()
	{
		return $this->oauth2_provider;
	}
}
