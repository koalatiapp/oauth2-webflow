<?php

declare(strict_types=1);

namespace Koalati\Test\OAuth2\Client\Provider;

use GuzzleHttp\ClientInterface;
use Koalati\OAuth2\Client\Provider\Exception\WebflowIdentityProviderException;
use Koalati\OAuth2\Client\Provider\Webflow;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;

class WebflowTest extends \PHPUnit\Framework\TestCase
{
	protected Webflow $provider;

	protected function setUp(): void
	{
		$this->provider = new Webflow([
			'clientId' => 'mock_client_id',
			'clientSecret' => 'mock_secret',
			'redirectUri' => 'none',
		]);
	}

	public function testGetBaseAuthorizationUrl(): void
	{
		$this->assertEquals('https://webflow.com/oauth/authorize', $this->provider->getBaseAuthorizationUrl());
	}

	public function testGetBaseAccessTokenUrl(): void
	{
		$this->assertEquals('https://api.webflow.com/oauth/access_token', $this->provider->getBaseAccessTokenUrl([]));
	}

	public function testGetResourceOwnerDetailsUrl(): void
	{
		$this->assertEquals('https://api.webflow.com/user', $this->provider->getResourceOwnerDetailsUrl($this->createMock(AccessToken::class)));
	}

	public function testGetRevokeUrl(): void
	{
		$this->assertEquals('https://api.webflow.com/oauth/revoke_authorization', $this->provider->getRevokeUrl());
	}

	public function testAuthorizationUrl()
	{
		$url = $this->provider->getAuthorizationUrl();
		$uri = parse_url($url);
		parse_str($uri['query'], $query);

		$this->assertArrayHasKey('client_id', $query);
		$this->assertArrayHasKey('redirect_uri', $query);
		$this->assertArrayHasKey('state', $query);
		$this->assertArrayHasKey('response_type', $query);
		$this->assertEquals('code', $query['response_type']);
	}

	public function testGetAccessToken()
	{
		/** @var MockObject|ResponseInterface $response */
		$response = $this->createMock(ResponseInterface::class);
		$response->expects($this->once())->method('getBody')->willReturn('{"access_token":"mock_access_token", "token_type":"bearer"}');
		$response->expects($this->once())->method('getHeader')->willReturn([
			'content-type' => 'json',
		]);
		$response->expects($this->once())->method('getStatusCode')->willReturn(200);

		/** @var MockObject|ClientInterface $client */
		$client = $this->createMock(ClientInterface::class);
		$client->expects($this->once())->method('send')->willReturn($response);
		$this->provider->setHttpClient($client);

		$token = $this->provider->getAccessToken('authorization_code', [
			'code' => 'mock_authorization_code',
		]);

		$this->assertEquals('mock_access_token', $token->getToken());
		$this->assertNull($token->getExpires());
		$this->assertNull($token->getRefreshToken());
	}

	public function testExceptionThrownUponOauthError()
	{
		/** @var MockObject|ResponseInterface $response */
		$response = $this->createMock(ResponseInterface::class);
		$response->method('getBody')->willReturn('{"error":"invalid_grant"}');
		$response->method('getHeader')->willReturn([
			'content-type' => 'json',
		]);
		$response->method('getStatusCode')->willReturn(400);

		/** @var MockObject|ClientInterface $client */
		$client = $this->createMock(ClientInterface::class);
		$client->expects($this->once())->method('send')->willReturn($response);
		$this->provider->setHttpClient($client);

		$this->expectException(WebflowIdentityProviderException::class);
		$this->expectExceptionMessage("Provided 'code' was invalid");

		$this->provider->getAccessToken('authorization_code', [
			'code' => 'mock_authorization_code',
		]);
	}
}
