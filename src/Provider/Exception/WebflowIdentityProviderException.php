<?php

declare(strict_types=1);

namespace Koalati\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class WebflowIdentityProviderException extends IdentityProviderException
{
	public function __construct(string $message, int $code, ResponseInterface $response)
	{
		parent::__construct("Webflow OAuth2 error: {$message}", $code, (string) $response->getBody());
	}
}
