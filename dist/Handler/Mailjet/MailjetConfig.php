<?php
namespace Coercive\Utility\Email\Handler\Mailjet;

/**
 * Mailjet Credentials Handler
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class MailjetConfig
{
	const DEFAULT_VERSION = 'v3.1';

	/** @var string */
	private $public;

	/** @var string */
	private $private;

	/** @var string */
	private $version;

	/** @var bool */
	private $sandbox;

	/**
	 * MailjetConfig constructor.
	 *
	 * @param string $public [optional]
	 * @param string $private [optional]
	 * @param string $version [optional]
	 * @param bool $sandbox [optional]
	 * @return void
	 */
	public function __construct(string $public = '', string $private = '', string $version = self::DEFAULT_VERSION, bool $sandbox = false)
	{
		$this->public = $public;
		$this->private = $private;
		$this->version = $version;
		$this->sandbox = $sandbox;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setPublicKey(string $key): MailjetConfig
	{
		$this->public = $key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPublicKey(): string
	{
		return $this->public;
	}

	/**
	 * @param string $key
	 * @return $this
	 */
	public function setPrivateKey(string $key): MailjetConfig
	{
		$this->private = $key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPrivateKey(): string
	{
		return $this->private;
	}

	/**
	 * @param string $num
	 * @return $this
	 */
	public function setVersion(string $num): MailjetConfig
	{
		$this->version = $num;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getVersion(): string
	{
		return $this->version;
	}

	/**
	 * @param bool $status
	 * @return $this
	 */
	public function setSandbox(bool $status): MailjetConfig
	{
		$this->sandbox = $status;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getSandbox(): bool
	{
		return $this->sandbox;
	}
}