<?php

namespace Youthweb\Api;

use Art4\JsonApiClient\Utils\Manager as JsonApiClientManager;
use Cache\Adapter\Void\VoidCachePool;
use DateInterval;
use DateTime;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Youthweb\Api\Exception\MissingCredentialsException;
use Youthweb\Api\Exception\UnauthorizedException;

/**
 * Simple PHP Youthweb client
 *
 * Website: http://github.com/youthweb/php-youthweb-api
 */
final class Client implements ClientInterface
{
	/**
	 * @var string
	 */
	private $api_version = '0.6';

	/**
	 * @var string
	 */
	private $api_domain = 'https://api.youthweb.net';

	/**
	 * @var string
	 */
	private $auth_domain = 'https://youthweb.net';

	/**
	 * @var HttpClientInterface
	 */
	private $http_client;

	/**
	 * @var League\OAuth2\Client\Provider\AbstractProvider
	 */
	private $oauth2_client;

	/**
	 * @var CacheItemPoolInterface
	 */
	private $cache_provider;

	/**
	 * @var string
	 */
	private $cache_namespace = 'php_youthweb_api.';

	/**
	 * @var array
	 */
	private $resources = [];

	/**
	 * @var RequestFactoryInterface
	 */
	private $request_factory;

	/**
	 * @var string
	 * @since Youthweb-API 0.6
	 */
	private $client_id;

	/**
	 * @var string
	 * @since Youthweb-API 0.6
	 */
	private $client_secret;

	/**
	 * @var string
	 * @since Youthweb-API 0.6
	 */
	private $redirect_url = '';

	/**
	 * @var string
	 * @deprecated Since Youthweb-API 0.6
	 */
	private $username = '';

	/**
	 * @var string
	 * @deprecated Since Youthweb-API 0.6
	 */
	private $token_secret = '';

	/**
	 * Constructs the Client.
	 *
	 * @param array $options An array of options to set on the client.
	 *     Options include `api_version`, `url` and `cache_namespace`.
	 * @param array $collaborators An array of collaborators that may be used to
	 *     override this provider's default behavior. Collaborators include
	 *     http_client` and `cache_provider`.
	 */
	public function __construct(array $options = [], array $collaborators = [])
	{
		foreach ($options as $option => $value)
		{
			if (in_array($option, [
				'api_version',
				'api_domain',
				'auth_domain',
				'cache_namespace',
				'client_id',
				'client_secret',
				'redirect_url',
			]))
			{
				$this->{$option} = (string) $value;
			}
		}

		if (empty($collaborators['http_client']))
		{
			$collaborators['http_client'] = new HttpClient(
				[
					// Guzzle config
				]
			);
		}

		$this->setHttpClient($collaborators['http_client']);

		if (empty($collaborators['oauth2_provider']))
		{
			$collaborators['oauth2_provider'] = new \Youthweb\OAuth2\Client\Provider\Youthweb([
				'clientId'     => $this->client_id,
				'clientSecret' => $this->client_secret,
				'redirectUri'  => $this->redirect_url,
				'apiDomain'    => $this->api_domain,
				'domain'       => $this->auth_domain,
			]);
		}

		$this->setOauth2Provider($collaborators['oauth2_provider']);

		if (empty($collaborators['cache_provider']))
		{
			$collaborators['cache_provider'] = new VoidCachePool();
		}

		$this->setCacheProvider($collaborators['cache_provider']);

		if (empty($collaborators['request_factory']))
		{
			$collaborators['request_factory'] = new RequestFactory();
		}

		$this->setRequestFactory($collaborators['request_factory']);

		if (empty($collaborators['resource_factory']))
		{
			$collaborators['resource_factory'] = new ResourceFactory();
		}

		$this->setResourceFactory($collaborators['resource_factory']);
	}

	/**
	 * @param string $name
	 *
	 * @return Resource\AbstractResource
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getResource($name)
	{
		if ( ! isset($this->resources[$name]) )
		{
			$this->resources[$name] = $this->getResourceFactory()->createResource($name, $this);
		}

		return $this->resources[$name];
	}

	/**
	 * Returns the Url
	 *
	 * @deprecated Will be set to private in future. Don't use it anymore
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->api_domain;
	}

	/**
	 * Set the Url
	 *
	 * @deprecated Will be set to private in future. Use the constructor instead
	 *
	 * @param string $url The url
	 * @return self
	 */
	public function setUrl($url)
	{
		$this->api_domain = (string) $url;

		return $this;
	}

	/**
	 * Set the User Credentials
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @param string $username The username
	 * @param string $token_secret The Token-Secret
	 * @return self
	 */
	public function setUserCredentials($username, $token_secret)
	{
		$this->username = strval($username);
		$this->token_secret = strval($token_secret);

		return $this;
	}

	/**
	 * Get a User Credentials
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @param string $key 'username' or 'token_secret'
	 * @return string the requested user credential
	 */
	public function getUserCredential($key)
	{
		$key = strval($key);

		if ( ! in_array($key, ['username', 'token_secret']) )
		{
			throw new \UnexpectedValueException('"' . $key . '" is not a valid key for user credentials.');
		}

		return $this->$key;
	}

	/**
	 * Authorize the client credentials
	 *
	 * @param array $params for authrization code: ['code' => 'authorization_code_from_callback_url...']
	 *
	 * @throws MissingCredentialsException If no user or client credentials are set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return void
	 */
	public function authorize(array $params = [])
	{
		if ( $this->client_id === null or $this->client_secret === null )
		{
			throw new MissingCredentialsException;
		}

		$access_token_item = $this->getCacheProvider()->getItem($this->buildCacheKey('access_token'));

		if ( $access_token_item->isHit() )
		{
			return;
		}

		$provider = $this->getOauth2Provider();

		$refresh_token_item = $this->getCacheProvider()->getItem($this->buildCacheKey('refresh_token'));

		if ( ! $refresh_token_item->isHit() )
		{
			if ( ! isset($params['code']) )
			{
				$options = [
					// TODO: Scope-Übergabe ermöglichen
					'scope' => 'user:email',
				];

				// If we don't have an authorization code then get one
				$auth_url = $provider->getAuthorizationUrl($options);

				throw UnauthorizedException::withAuthorizationUrl($auth_url);
			}
			else
			{
				// Try to get an access token (using the authorization code grant)
				$token = $provider->getAccessToken('authorization_code', [
					'code' => $params['code'],
				]);
			}
		}
		else
		{
			// TODO: Prüfen, ob Exception geworfen wird, bzw wenn refresh_token nichts gebracht hat
			$token = $provider->getAccessToken('refresh_token', [
				'refresh_token' => $refresh_token_item->get(),
			]);
		}

		$access_token_item->set($token->getToken());
		$date = new DateTime('@'.$token->getExpires());
		$access_token_item->expiresAt($date);
		$this->getCacheProvider()->saveDeferred($access_token_item);

		$refresh_token_item->set($token->getRefreshToken());
		// refresh_token sind 30 Tage gültig
		$refresh_token_item->expiresAfter(new DateInterval('P30D'));
		$this->getCacheProvider()->saveDeferred($refresh_token_item);

		$this->getCacheProvider()->commit();
	}

	/**
	 * HTTP GETs a json $path and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @throws MissingCredentialsException If no user or client credentials are set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return array
	 */
	public function get($path, array $data = [])
	{
		$data['headers']['Authorization'] = $this->getBearerToken();

		return $this->runRequest($path, 'GET', $data);
	}

	/**
	 * HTTP GETs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function getUnauthorized($path, array $data = [])
	{
		return $this->runRequest($path, 'GET', $data);
	}

	/**
	 * HTTP POSTs a json $path without Authorization and decodes it to an object
	 *
	 * @param string  $path
	 * @param array   $data
	 *
	 * @return array
	 */
	public function postUnauthorized($path, array $data = [])
	{
		return $this->runRequest($path, 'POST', $data);
	}

	/**
	 * Set a http client
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @param HttpClientInterface $client the http client
	 * @return self
	 */
	public function setHttpClient(HttpClientInterface $client)
	{
		$this->http_client = $client;

		return $this;
	}

	/**
	 * Set a oauth2 provider
	 *
	 * @param League\OAuth2\Client\Provider\AbstractProvider $oauth2_provider the oauth2 provider
	 * @return self
	 */
	public function setOauth2Provider(\League\OAuth2\Client\Provider\AbstractProvider $oauth2_provider)
	{
		$this->oauth2_provider = $oauth2_provider;

		return $this;
	}

	/**
	 * Get the oauth2 provider
	 *
	 * @return League\OAuth2\Client\Provider\AbstractProvider the oauth2 provider
	 */
	public function getOauth2Provider()
	{
		return $this->oauth2_provider;
	}

	/**
	 * Set a cache provider
	 *
	 * @deprecated Since Youthweb-API 0.6
	 *
	 * @param Psr\Cache\CacheItemPoolInterface $cache_provider the cache provider
	 * @return self
	 */
	public function setCacheProvider(CacheItemPoolInterface $cache_provider)
	{
		$this->cache_provider = $cache_provider;

		return $this;
	}

	/**
	 * Get the cache provider
	 *
	 * @return Psr\Cache\CacheItemPoolInterface the cache provider
	 */
	public function getCacheProvider()
	{
		return $this->cache_provider;
	}

	/**
	 * Build a cache key
	 *
	 * @param string $key The key
	 * @return stirng The cache key
	 **/
	public function buildCacheKey($key)
	{
		return $this->cache_namespace . strval($key);
	}

	/**
	 * Get the Bearer Token
	 *
	 * @throws MissingCredentialsException If no user or client credentials are set
	 * @throws UnauthorizedException contains the url to get an authorization code
	 *
	 * @return string The Bearer token incl. type e.g. "Bearer jcx45..."
	 */
	private function getBearerToken()
	{
		try
		{
			$this->authorize();

			$access_token_item = $this->getCacheProvider()->getItem($this->buildCacheKey('access_token'));

			return 'Bearer ' . $access_token_item->get();
		}
		catch (MissingCredentialsException $e)
		{
			return $this->getResource('auth')->getBearerToken();
		}
	}

	/**
	 * @param string $path
	 * @param string $method
	 * @param array  $data
	 *
	 * @return mixed
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	private function runRequest($path, $method = 'GET', array $data = [])
	{
		$request = $this->createRequest($method, $this->getUrl() . $path, $data);

		try
		{
			$response = $this->getHttpClient()->send($request);
		}
		catch (\Exception $e)
		{
			throw $this->handleClientException($e);
		}

		return $this->parseResponse($response);
	}

	/**
	 * Creates a PSR-7 request instance.
	 *
	 * @param  string $method
	 * @param  string $url
	 * @param  array $options
	 * @return RequestInterface
	 */
	private function createRequest($method, $url, array $options)
	{
		$options = $this->parseOptions($options);

		$default_headers = [
			'Content-Type' => 'application/vnd.api+json',
			'Accept' => 'application/vnd.api+json, application/vnd.api+json; net.youthweb.api.version=' . $this->api_version,
		];

		$headers = array_merge($default_headers, $options['headers']);

		return $this->getRequestFactory()->createRequest($method, $url, $headers, $options['body'], $options['version']);
	}

	/**
	 * Parses simplified options.
	 *
	 * @param array $options Simplified options.
	 *
	 * @return array Extended options for use with getRequest.
	 */
	private function parseOptions(array $options)
	{
		// Should match default values for getRequest
		$defaults = [
			'headers' => [],
			'body'    => null,
			'version' => '1.1',
		];

		return array_merge($defaults, $options);
	}

	/**
	 * @param ResponseInterface $response
	 *
	 * @return \Art4\JsonApiClient\Document
	 *
	 * @throws \Exception If anything goes wrong on the request
	 */
	private function parseResponse(ResponseInterface $response)
	{
		$body = $response->getBody()->getContents();

		return (new JsonApiClientManager())->parse($body);
	}

	/**
	 * Returns the http client
	 *
	 * @return HttpClientInterface The Http client
	 */
	private function getHttpClient()
	{
		return $this->http_client;
	}

	/**
	 * Set a request_factory
	 *
	 * @param RequestFactoryInterface $request_factory the request factory
	 * @return self
	 */
	private function setRequestFactory(RequestFactoryInterface $request_factory)
	{
		$this->request_factory = $request_factory;

		return $this;
	}

	/**
	 * Get the request factory
	 *
	 * @return RequestFactoryInterface the request factory
	 */
	private function getRequestFactory()
	{
		return $this->request_factory;
	}

	/**
	 * Set a resource_factory
	 *
	 * @param ResourceFactoryInterface $resource_factory the resource factory
	 * @return self
	 */
	private function setResourceFactory(ResourceFactoryInterface $resource_factory)
	{
		$this->resource_factory = $resource_factory;

		return $this;
	}

	/**
	 * Get the resource factory
	 *
	 * @return ResourceFactoryInterface the resource factory
	 */
	private function getResourceFactory()
	{
		return $this->resource_factory;
	}

	/**
	 * Handels a Exception from the Client
	 *
	 * @param \Exception $e The exception
	 * @return \Exception An exception for re-throwing
	 **/
	private function handleClientException(\Exception $e)
	{
		$message = null;
		$response = null;

		// Try to get the response
		if ( $e instanceof ClientException or is_callable([$e, 'getResponse']) )
		{
			$response = $e->getResponse();
		}

		if ( is_object($response) and $response instanceof ResponseInterface )
		{
			$document = $this->parseResponse($response);

			// Get an error message from the json api body
			if ( $document->has('errors.0') )
			{
				$error = $document->get('errors.0');

				if ( $error->has('detail') )
				{
					$message = $error->get('detail');
				}
				elseif ( $error->has('title') )
				{
					$message = $error->get('title');
				}
			}
		}

		if ( is_null($message) )
		{
			$message = 'The server responses with an unknown error.';
		}

		return new \Exception($message, $e->getCode(), $e);
	}

	/**
	 * destructor
	 **/
	public function __destruct()
	{
		// Save deferred items
		$this->getCacheProvider()->commit();
	}
}
