<?php
namespace Coercive\Utility\Email\Handler\SwiftMailer;

/**
 * Swift Mailer Credentials Handler
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class SwiftMailerConfig
{
	/** @var string */
	private $host = '';

	/** @var int */
	private $port = 0;

	/** @var string */
	private $username = '';

	/** @var string */
	private $password = '';

	/** @var string */
	private $secure = '';

	/**
	 * SwiftMailerConfig constructor.
	 *
	 * @param string $host [optional]
	 * @param int $port [optional]
	 * @param string $username [optional]
	 * @param string $password [optional]
	 * @param string $secure [optional]
	 * @return void
	 */
	public function __construct(string $host = '', int $port = 0, string $username = '', string $password = '', string $secure = '')
	{
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->secure = $secure;
	}

	/**
	 * @param string $host
	 * @return $this
	 */
	public function setHost(string $host): SwiftMailerConfig
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * @param int $port
	 * @return $this
	 */
	public function setPort(int $port): SwiftMailerConfig
	{
		$this->port = $port;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getPort(): int
	{
		return $this->port;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setUsername(string $name): SwiftMailerConfig
	{
		$this->username = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUsername(): string
	{
		return $this->username;
	}

	/**
	 * @param string $pw
	 * @return $this
	 */
	public function setPassword(string $pw): SwiftMailerConfig
	{
		$this->password = $pw;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @param string $secure
	 * @return $this
	 */
	public function setSecure(string $secure): SwiftMailerConfig
	{
		$this->secure = $secure;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSecure(): string
	{
		return $this->secure;
	}
}