<?php

namespace Youthweb\Api\Tests\Unit;

use Youthweb\Api\YouthwebAuthenticator;

class YouthwebAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Create a Authenticator with mocks of all collaborators
	 */
	private function createAuthenticator(array $options = [], array $collaborators = [])
	{
		$default_options = [];

		$options = array_merge($default_options, $options);

		$default_collaborators = [
			'oauth2_provider' => $this->createMock('Youthweb\OAuth2\Client\Provider\Youthweb'),
		];

		$collaborators = array_merge($default_collaborators, $collaborators);

		return new YouthwebAuthenticator($options, $collaborators);
	}

	/**
	 * @test
	 */
	public function testGetAuthorizationUrlReturnsUrl()
	{
		$oauth2_provider = $this->createMock('Youthweb\OAuth2\Client\Provider\Youthweb');

		$url = 'https://example.org';

		$oauth2_provider->expects($this->once())
			->method('getAuthorizationUrl')
			->willReturn($url);

		$authenticator = $this->createAuthenticator([], [
			'oauth2_provider' => $oauth2_provider,
		]);

		$this->assertSame($url, $authenticator->getAuthorizationUrl());
	}

	/**
	 * @test
	 */
	public function testGetStateReturnsState()
	{
		$oauth2_provider = $this->createMock('Youthweb\OAuth2\Client\Provider\Youthweb');

		$state = 'random_string';

		$oauth2_provider->expects($this->once())
			->method('getState')
			->willReturn($state);

		$authenticator = $this->createAuthenticator([], [
			'oauth2_provider' => $oauth2_provider,
		]);

		$this->assertSame($state, $authenticator->getState());
	}

	/**
	 * @test
	 */
	public function testGetStateWorkaroundReturnsState()
	{
		$oauth2_provider = $this->createMock('Youthweb\OAuth2\Client\Provider\Youthweb');

		$state = 'random_string';

		$oauth2_provider->expects($this->exactly(2))
			->method('getState')
			->will($this->onConsecutiveCalls(null, $state));

		$authenticator = $this->createAuthenticator([], [
			'oauth2_provider' => $oauth2_provider,
		]);

		$this->assertSame($state, $authenticator->getState());
	}

	/**
	 * @test
	 */
	public function testGetAccessTokenWithWrongGrantThrowsException()
	{
		$authenticator = $this->createAuthenticator();

		$this->setExpectedException(
			'InvalidArgumentException',
			'Unsupported grant "wrong_grant"'
		);

		$authenticator->getAccessToken('wrong_grant');
	}

	/**
	 * @test
	 */
	public function testGetAccessTokenWithAuthCodeAndStateSavesToken()
	{
		$oauth2_provider = $this->createMock('Youthweb\OAuth2\Client\Provider\Youthweb');
		$access_token = $this->createMock('League\OAuth2\Client\Token\AccessToken');

		$oauth2_provider->expects($this->once())
			->method('getAccessToken')
			->willReturn($access_token);

		$authenticator = $this->createAuthenticator(
			[
				'client_id'     => 'client_id',
				'client_secret' => 'client_secret',
				'redirect_url'  => 'https://example.org/callback',
			],
			[
				'oauth2_provider' => $oauth2_provider,
			]
		);

		$this->assertSame($access_token,
			$authenticator->getAccessToken('authorization_code', [
				'code' => 'auth_code',
				'state' => 'random_string',
			])
		);
	}
}
