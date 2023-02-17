# Webflow Provider for OAuth 2.0 client `league/oauth2-client`

[![Latest Version](https://img.shields.io/github/release/koalatiapp/oauth2-webflow.svg?style=flat-square)](https://github.com/koalatiapp/oauth2-webflow/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/koalatiapp/oauth2-webflow/main.svg?style=flat-square)](https://travis-ci.org/koalatiapp/oauth2-webflow)
[![Total Downloads](https://img.shields.io/packagist/dt/koalati/oauth2-webflow.svg?style=flat-square)](https://packagist.org/packages/koalati/oauth2-webflow)

This package provides Webflow OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Requirements

This package requires PHP `8.0` or above.


## Installation

To install, use composer:

```
composer require koalati/oauth2-webflow
```

## Usage

### Authorization Code Flow

```php
<?php

use Koalati\OAuth2\Client\Provider\Webflow;

session_start();

$provider = new Webflow([
	// @TODO Fill these based on your app's configuration
	/**
	 * @see https://developers.webflow.com/docs/getting-started-with-apps#step-2-get-your-client-id-and-secret)
	 * @see https://developers.webflow.com/docs/oauth#user-authorization
	 */
	'clientId'          => '{webflow-app-id}',
	'clientSecret'      => '{webflow-app-secret}',
	'redirectUri'       => 'https://example.com/callback-url',
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {
	$authUrl = $provider->getAuthorizationUrl();
	$_SESSION['oauth2state'] = $provider->getState();
	
	echo "<a href='{$authUrl}'>Log in with Webflow</a>";
	exit;
}

// Check given state against previously stored one to mitigate CSRF attack
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
	unset($_SESSION['oauth2state']);

	http_response_code(403);
	echo 'Invalid state / CSRF token.';
	exit;
}

// Try to get an access token (using the authorization code grant)
$token = $provider->getAccessToken('authorization_code', [
	'code' => $_GET['code']
]);

// At this point, you have an access token you can use to interact with the API.
// You can use it to look up the user's information, or to make any other API calls.
try {
	// We got an access token, let's now get the user's details
	$user = $provider->getResourceOwner($token);

	// Use these details to create a new profile
	printf('<h1>Hello %s!</h1>', $user->getFirstName());
	
	echo "<strong>Your Webflow user info:</strong><br>";
	echo '<pre>';
	print_r($user);
	echo '</pre>';

} catch (\Exception $e) {
	// Failed to get user details
	exit("An error has occured while fetching the Webflow user's information.");
}

echo "<strong>Your Webflow access token:</strong> (keep this safe!)<br>";
echo '<pre>';
// Use this to interact with an API on the users behalf
echo $token->getToken();
echo '</pre>';
```

### Revoke Code Flow

```php
<?php

use Koalati\OAuth2\Client\Provider\Webflow;

$provider = new Webflow([
	// @TODO Fill these based on your app's configuration
	/**
	 * @see https://developers.webflow.com/docs/getting-started-with-apps#step-2-get-your-client-id-and-secret)
	 * @see https://developers.webflow.com/docs/oauth#user-authorization
	 */
	'clientId'          => '{webflow-app-id}',
	'clientSecret'      => '{webflow-app-secret}',
	'redirectUri'       => 'https://example.com/callback-url',
]);

// Use the token of "Authorization Code Flow" which you saved somewhere for the user
$token = $token->getToken();

try {
	$provider->revokeAccessToken($token);
} catch (Exception $e) {
	exit('Failed to revoke the Webflow access token.');
}
```


## Contributing

Please see [CONTRIBUTING](https://github.com/koalatiapp/oauth2-webflow/blob/main/CONTRIBUTING.md) for details.


## Credits

The core of this package was developed by [Koalati](https://www.koalati.com/), 
a QA platform for web developers and agencies.

Check out other contributors who helped maintain and make this package better: [All Contributors](https://github.com/koalatiapp/oauth2-webflow/contributors).


## License

The MIT License (MIT). Please see [License File](https://github.com/koalatiapp/oauth2-webflow/blob/main/LICENSE) for more information.