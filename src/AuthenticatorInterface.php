<?php

namespace Youthweb\Api;

/**
 * Interface for authenticator
 */
interface AuthenticatorInterface
{
	/**
	 * Constructs the Authenticator
	 *
	 * @param array $options An array of options to set on the client.
	 *     Options include `api_version`, `api_domain`, `auth_domain`,
	 *     `cache_namespace`, `client_id`, `client_secret` and `redirect_url`.
	 * @param array $collaborators An array of collaborators that may be used to
	 *     override this provider's default behavior. Collaborators include
	 *     http_client`, `oauth2_provider`, `cache_provider`, `request_factory`
	 *     and `resource_factory`.
	 */
	public function __construct(array $options = [], array $collaborators = []);

	/**
	 * get the authorization url
	 *
	 * @param  array $options
	 * @return string
	 */
	public function getAuthorizationUrl(array $options = []);

	/**
	 * get a random state
	 *
	 * @return string
	 */
	public function getState();

	/**
	 * Get an access token
	 *
	 * @param string $grant the grant, e.g. `authorization_code`
	 * @param array $params for authorization code:
	 * [
	 *     'code' => 'authorization_code_from_callback_url...',
	 *     'state' => 'state_from_callback_url_for_csrf_protection',
	 * ]
	 *
	 * @throws InvalidArgumentException If a wrong state was set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return void
	 */
	public function getAccessToken($grant, array $params = []);
}
