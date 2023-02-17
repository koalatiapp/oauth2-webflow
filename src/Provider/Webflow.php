<?php

declare(strict_types=1);

namespace Koalati\OAuth2\Client\Provider;

use Koalati\OAuth2\Client\Provider\Exception\WebflowIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use LogicException;
use Psr\Http\Message\ResponseInterface;

/**
 * OAuth2 provider for Webflow.
 *
 * @see https://developers.webflow.com/docs/oauth
 */
class Webflow extends AbstractProvider
{
	use ArrayAccessorTrait;
	use BearerAuthorizationTrait;

	/**
	 * Webflow uses a different URI for the initial authorization call.
	 *
	 * @see https://developers.webflow.com/docs/oauth#user-authorization
	 */
	public const AUTHORIZATION_BASE_URI = 'https://webflow.com';

	/**
	 * Base URI for OAuth endpoints, other than the initial authorization.
	 *
	 * @see https://developers.webflow.com/docs/oauth
	 */
	public const OAUTH_BASE_URI = 'https://api.webflow.com';

	/**
	 * Webflow only supports the Authorization Code flow ype.
	 *
	 * @see https://developers.webflow.com/docs/oauth
	 */
	public const FLOW_TYPE = 'authorization_code';

	/**
	 * Get authorization URL to begin OAuth flow
	 *
	 * @see https://developers.webflow.com/docs/oauth#user-authorization
	 */
	public function getBaseAuthorizationUrl(): string
	{
		return $this->buildUrl('/oauth/authorize');
	}

	/**
	 * Get access token URL to retrieve token
	 *
	 * @param array<string,mixed> $params
	 * @see https://developers.webflow.com/docs/oauth#access-token
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.params)
	 */
	public function getBaseAccessTokenUrl(array $params): string
	{
		return $this->buildUrl('/oauth/access_token');
	}

	/**
	 * Get provider URL to fetch user details
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.token)
	 */
	public function getResourceOwnerDetailsUrl(AccessToken $token): string
	{
		return $this->buildUrl('/user');
	}

	/**
	 * Get authorization revocation URL.
	 *
	 * @see https://developers.webflow.com/docs/oauth#revoke-authorization
	 */
	public function getRevokeUrl(): string
	{
		return $this->buildUrl('/oauth/revoke_authorization');
	}

	/**
	 * Revokes access for a specified token.
	 *
	 * @return bool Returns `true` when the token has been revoked successfully.
	 */
	public function revokeAccessToken(string $token): bool
	{
		$method = $this->getAccessTokenMethod();
		$url = $this->getRevokeUrl();
		$options = $this->optionProvider->getAccessTokenOptions($method, [
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'access_token' => $token,
		]);

		$request = $this->getRequest($method, $url, $options);
		/** @var array<string,mixed> $response */
		$response = $this->getParsedResponse($request);

		return (bool) ($response['did_revoke'] ?? false);
	}

	/**
	 * Requests and returns the resource owner of given access token.
	 */
	public function getResourceOwner(AccessToken $token): WebflowResourceOwner
	{
		/** @var array{user:array<string,string>} $response */
		$response = $this->fetchResourceOwnerDetails($token);

		return $this->createResourceOwner($response, $token);
	}

	/**
	 * Returns authorization parameters based on provided options.
	 *
	 * @param  array<string,mixed> $options
	 * @return array<string,mixed> Authorization parameters
	 */
	protected function getAuthorizationParameters(array $options): array
	{
		$options = parent::getAuthorizationParameters($options);
		$options['type'] = self::FLOW_TYPE;

		return $options;
	}

	/**
	 * Get the default scopes used by this provider.
	 *
	 * This should not be a complete list of all scopes, but the minimum
	 * required for the provider user interface!
	 *
	 * @return array{}
	 */
	protected function getDefaultScopes(): array
	{
		return [];
	}

	/**
	 * Builds the URL for a given endpoint with the provided query parameters.
	 */
	protected function buildUrl(string $endpoint): string
	{
		$baseUri = self::OAUTH_BASE_URI;

		if ($endpoint === '/oauth/authorize') {
			$baseUri = self::AUTHORIZATION_BASE_URI;
		}

		return rtrim($baseUri, '/') . '/' . ltrim($endpoint, '/');
	}

	/**
	 * Returns the string that should be used to separate scopes when building
	 * the URL for requesting an access token.
	 *
	 * @return string Scope separator, defaults to ' '
	 */
	protected function getScopeSeparator(): string
	{
		return ' ';
	}

	/**
	 * Check a provider response for errors.
	 *
	 * @param array<string,mixed>|string $data Parsed response data
	 *
	 * @throws WebflowIdentityProviderException
	 *
	 * @see https://developers.webflow.com/docs/oauth#oauth-errors
	 */
	protected function checkResponse(ResponseInterface $response, $data): void
	{
		if ($response->getStatusCode() !== 200) {
			if (isset($data['unsupported_grant_type'])) {
				throw new LogicException("'grant_type' should always be specified as the string 'authorization_code'.");
			}

			if (isset($data['invalid_client'])) {
				throw new WebflowIdentityProviderException('No application found matching the provided credentials.', $response->getStatusCode(), $response);
			}

			if (isset($data['invalid_grant'])) {
				throw new WebflowIdentityProviderException("Provided 'code' was invalid", $response->getStatusCode(), $response);
			}

			throw new WebflowIdentityProviderException('Unexpected response', $response->getStatusCode(), $response);
		}
	}

	/**
	 * Generate a user object from a successful user details request.
	 *
	 * @param array{user:array<string,string>} $response
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.token)
	 */
	protected function createResourceOwner(array $response, AccessToken $token): WebflowResourceOwner
	{
		return new WebflowResourceOwner($response);
	}
}
