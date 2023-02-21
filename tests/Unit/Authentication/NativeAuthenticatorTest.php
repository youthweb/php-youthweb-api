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

namespace Youthweb\Api\Tests\Unit\Authentication;

use PHPUnit\Framework\TestCase;
use Youthweb\Api\Authentication\NativeAuthenticator;
use Youthweb\OAuth2\Client\Provider\Youthweb;

class YouthwebAuthenticatorTest extends TestCase
{
    /**
     * Create a Authenticator with mocks of all collaborators
     */
    private function createAuthenticator(array $options = [], array $collaborators = [])
    {
        $default_options = [];

        $options = array_merge($default_options, $options);

        $default_collaborators = [
            'oauth2Provider' => $this->createMock(Youthweb::class),
        ];

        $collaborators = array_merge($default_collaborators, $collaborators);

        return new NativeAuthenticator($options, $collaborators);
    }

    /**
     * @test
     */
    public function testGetAuthorizationUrlReturnsUrl(): void
    {
        $oauth2Provider = $this->createMock(Youthweb::class);

        $url = 'https://example.org';

        $oauth2Provider->expects($this->once())
            ->method('getAuthorizationUrl')
            ->willReturn($url);

        $authenticator = $this->createAuthenticator([], [
            'oauth2Provider' => $oauth2Provider,
        ]);

        $this->assertSame($url, $authenticator->getAuthorizationUrl());
    }

    /**
     * @test
     */
    public function testGetStateReturnsState(): void
    {
        $oauth2Provider = $this->createMock(Youthweb::class);

        $state = 'random_string';

        $oauth2Provider->expects($this->once())
            ->method('getState')
            ->willReturn($state);

        $authenticator = $this->createAuthenticator([], [
            'oauth2Provider' => $oauth2Provider,
        ]);

        $this->assertSame($state, $authenticator->getState());
    }

    /**
     * @test
     */
    public function testGetStateWorkaroundReturnsState(): void
    {
        $oauth2Provider = $this->createMock(Youthweb::class);

        $oauth2Provider->expects($this->once())
            ->method('getAuthorizationUrl')
            ->willReturn('');

        $state = 'random_string';

        $oauth2Provider->expects($this->exactly(2))
            ->method('getState')
            ->will($this->onConsecutiveCalls('', $state));

        $authenticator = $this->createAuthenticator([], [
            'oauth2Provider' => $oauth2Provider,
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
        $oauth2Provider = $this->createMock(Youthweb::class);
        $access_token = $this->createMock('League\OAuth2\Client\Token\AccessToken');

        $oauth2Provider->expects($this->once())
            ->method('getAccessToken')
            ->willReturn($access_token);

        $authenticator = $this->createAuthenticator(
            [
                'clientId'     => 'client_id',
                'clientSecret' => 'client_secret',
                'redirectUrl'  => 'https://example.org/callback',
            ],
            [
                'oauth2Provider' => $oauth2Provider,
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
