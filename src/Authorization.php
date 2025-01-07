<?php

namespace Mirarus\TeamSpeakCFDNS;

/**
 * Authorization
 *
 * @package    Mirarus\TeamSpeakCFDNS
 * @author     Ali Güçlü <aliguclutr@gmail.com>
 * @copyright  Copyright (c) 2025
 * @license    MIT
 * @version    1.0.0
 * @since      1.0.0
 */
class Authorization
{
	private $email;
	private $apiKey;

	/**
	 * @param $email
	 * @param $apiKey
	 */
	public function __construct($email, $apiKey)
	{
		$this->email = $email;
		$this->apiKey = $apiKey;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return mixed
	 */
	public function getApiKey()
	{
		return $this->apiKey;
	}
}