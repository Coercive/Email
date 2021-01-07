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

	/** @var string */
	private $dkim_domain = '';

	/** @var string */
	private $dkim_private = '';

	/** @var string */
	private $dkim_private_string = '';

	/** @var string */
	private $dkim_selector = 'mail';

	/** @var string */
	private $dkim_passphrase = '';

	/** @var string */
	private $dkim_identity = '';

	/** @var string */
	private $encoding = '';

	/** @var string */
	private $priority = 3;

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

	/**
	 * @param string $domain
	 * @return $this
	 */
	public function setDkimDomain(string $domain): PHPMailerConfig
	{
		$this->dkim_domain = $domain;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDkimDomain(): string
	{
		return $this->dkim_domain;
	}

	/**
	 * @param string $path
	 * @return $this
	 */
	public function setDkimPrivate(string $path): PHPMailerConfig
	{
		$this->dkim_private = $path;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDkimPrivate(): string
	{
		return $this->dkim_private;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setDkimPrivateString(string $key): PHPMailerConfig
	{
		$this->dkim_private_string = $key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDkimPrivateString(): string
	{
		return $this->dkim_private_string;
	}

	/**
	 * @param string $selector
	 * @return $this
	 */
	public function setDkimSelector(string $selector): PHPMailerConfig
	{
		$this->dkim_selector = $selector;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDkimSelector(): string
	{
		return $this->dkim_selector;
	}

	/**
	 * @param string $pw
	 * @return $this
	 */
	public function setDkimPassphrase(string $pw): PHPMailerConfig
	{
		$this->dkim_passphrase = $pw;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDkimPassphrase(): string
	{
		return $this->dkim_passphrase;
	}

	/**
	 * @param string $from
	 * @return $this
	 */
	public function setDkimIdentity(string $from): PHPMailerConfig
	{
		$this->dkim_identity = $from;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDkimIdentity(): string
	{
		return $this->dkim_identity;
	}

	/**
	 * @param string $method
	 * @return $this
	 */
	public function setEncoding(string $method): PHPMailerConfig
	{
		$this->encoding = $method;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEncoding(): string
	{
		return $this->encoding;
	}

	/**
	 * @param int $level
	 * @return $this
	 */
	public function setPriority(int $level): PHPMailerConfig
	{
		$this->priority = $level;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPriority(): string
	{
		return $this->encoding;
	}
}