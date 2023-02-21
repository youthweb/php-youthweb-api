<?php

declare(strict_types=1);
/*
 * PHP Youthweb API is an object-oriented wrapper for PHP of the Youthweb API.
 * Copyright (C) 2015-2019  Youthweb e.V.  https://youthweb.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Youthweb\Api\Tests\Unit;

use Youthweb\Api\YouthwebAuthenticator;

class YouthwebAuthenticatorTest extends \PHPUnit\Framework\TestCase
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
    public function testGetAuthorizationUrlReturnsUrl(): void
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
    public function testGetStateReturnsState(): void
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
    public function testGetStateWorkaroundReturnsState(): void
    {
        $oauth2_provider = $this->createMock('Youthweb\OAuth2\Client\Provider\Youthweb');

        $state = 'random_string';

        $oauth2_provider->expects($this->exactly(2))
            ->method('getState')
            ->will($this->onConsecutiveCalls('', $state));

        $authenticator = $this->createAuthenticator([], [
            'oauth2_provider' => $oauth2_provider,
        ]);

        $this->assertSame($state, $authenticator->getState());
    }

    /**
     * @test
     */
    public function testGetAccessTokenWithWrongGrantThrowsException(): void
    {
        $authenticator = $this->createAuthenticator();

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unsupported grant "wrong_grant"');

        $authenticator->getAccessToken('wrong_grant');
    }

    /**
     * @test
     */
    public function testGetAccessTokenWithAuthCodeAndStateSavesToken(): void
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

        $this->assertSame(
            $access_token,
            $authenticator->getAccessToken('authorization_code', [
                'code' => 'auth_code',
                'state' => 'random_string',
            ])
        );
    }
}
