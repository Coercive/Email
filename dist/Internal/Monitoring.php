<?php
namespace Coercive\Utility\Email\Internal;

/**
 * Email Monitoring Callback Handler
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Monitoring
{
	/** @var mixed */
	private $id = null;

	/** @var bool */
	private $create = null;

	/** @var string */
	private $update = null;

	/**
	 * @return $this
	 */
	public function reset(): Monitoring
	{
		$this->id = null;
		return $this;
	}

	/**
	 * @param callable $function
	 * @return $this
	 */
	public function setCreateCallback(callable $function): Monitoring
	{
		$this->create = $function;
		return $this;
	}

	/**
	 * @param Param $param
	 * @return $this
	 */
	public function create(Param $param): Monitoring
	{
		$this->reset();
		if($this->create) {
			$this->id = ($this->create)($param);
		}
		return $this;
	}

	/**
	 * @param callable $function
	 * @return $this
	 */
	public function setUpdateCallback(callable $function): Monitoring
	{
		$this->update = $function;
		return $this;
	}

	/**
	 * @param Status $status
	 * @param Param $param
	 * @return $this
	 */
	public function update(Status $status, Param $param): Monitoring
	{
		if($this->update && $this->id) {
			($this->update)($this->id, $status, $param);
		}
		return $this;
	}
}