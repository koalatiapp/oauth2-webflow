<?php

declare(strict_types=1);

namespace Koalati\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * OAuth2 resource owner for Webflow.
 *
 * @see https://developers.webflow.com/reference/get-authorized-user
 * @SuppressWarnings(PHPMD.ShortVariable.id)
 */
class WebflowResourceOwner implements ResourceOwnerInterface
{
	private string $id;

	private string $email;

	private string $firstName;

	private string $lastName;

	/**
	 * @param array{user:array<string,string>} $response
	 */
	public function __construct(array $response)
	{
		$this->id = $response['user']['_id'];
		$this->email = $response['user']['email'];
		$this->firstName = $response['user']['firstName'];
		$this->lastName = $response['user']['lastName'];
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getFirstName(): string
	{
		return $this->firstName;
	}

	public function getLastName(): string
	{
		return $this->lastName;
	}

	/**
	 * Return all of the owner details available as an array.
	 *
	 * @return array{_id:string,email:string,firstName:string,lastName:string}
	 */
	public function toArray(): array
	{
		return [
			'_id' => $this->id,
			'email' => $this->email,
			'firstName' => $this->firstName,
			'lastName' => $this->lastName,
		];
	}
}
