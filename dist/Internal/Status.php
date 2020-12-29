<?php
namespace Coercive\Utility\Email\Internal;

/**
 * Email status
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Status
{
	/** @var bool */
	private $status = false;

	/** @var string */
	private $code = '';

	/** @var string */
	private $message = '';

	/** @var string */
	private $trace = '';

	/** @var array */
	private $debug = [];

	/**
	 * @param bool $status
	 * @return $this
	 */
	public function setStatus(bool $status): Status
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function getStatus(): bool
	{
		return $this->status;
	}

	/**
	 * @return bool
	 */
	public function isFailure(): bool
	{
		return false === $this->status;
	}

	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return true === $this->status;
	}

	/**
	 * @param string $code
	 * @return $this
	 */
	public function setCode(string $code): Status
	{
		$this->code = $code;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage(string $message): Status
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @param string $trace
	 * @return $this
	 */
	public function setTrace(string $trace): Status
	{
		$this->trace = $trace;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTrace(): string
	{
		return $this->trace;
	}

	/**
	 * @param string $entry
	 * @return Status
	 */
	public function addDebug(string $entry): Status
	{
		$this->debug[] = $entry;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDebug(): string
	{
		return json_encode($this->debug);
	}
}