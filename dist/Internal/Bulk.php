<?php
namespace Coercive\Utility\Email\Internal;

/**
 * Bulk handler
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Bulk
{
	/** @var Param */
	private $global = null;

	/** @var Param[] */
	private $bulk = [];

	/**
	 * @param Param $param
	 * @return $this
	 */
	public function setGlobal(Param $param): Bulk
	{
		$this->global = $param;
		return $this;
	}

	/**
	 * @return Param
	 */
	public function getGlobal()
	{
		return $this->global;
	}

	/**
	 * @param Param $param
	 * @return $this
	 */
	public function addBulk(Param $param): Bulk
	{
		$this->bulk[] = $param;
		return $this;
	}

	/**
	 * @return Param[]
	 */
	public function getBulk(): array
	{
		return $this->bulk;
	}
}