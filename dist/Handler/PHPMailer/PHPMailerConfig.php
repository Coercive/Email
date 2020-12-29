<?php
namespace Coercive\Utility\Email\Handler\PHPMailer;

/**
 * PHPMailer Credentials Handler
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class PHPMailerConfig
{
	/** @var bool */
	private $debug = false;

	/** @var string */
	private $host = '';

	/** @var int */
	private $port = 0;

	/** @var string */
	private $username = '';

	/** @var string */
	private $password = '';

	/**
	 * PHPMailerConfig constructor.
	 *
	 * @param bool $debug [optional]
	 * @param string $host [optional]
	 * @param int $port [optional]
	 * @param string $username [optional]
	 * @param string $password [optional]
	 * @return void
	 */
	public function __construct(bool $debug = false, string $host = '', int $port = 0, string $username = '', string $password = '')
	{
		$this->debug = $debug;
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * @param bool $status
	 * @return $this
	 */
	public function setDebug(bool $status): PHPMailerConfig
	{
		$this->debug = $status;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getDebug(): bool
	{
		return $this->debug;
	}

	/**
	 * @param string $host
	 * @return $this
	 */
	public function setHost(string $host): PHPMailerConfig
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
	public function setPort(int $port): PHPMailerConfig
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
	public function setUsername(string $name): PHPMailerConfig
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
	public function setPassword(string $pw): PHPMailerConfig
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
}