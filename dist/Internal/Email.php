<?php
namespace Coercive\Utility\Email\Internal;

/**
 * Email handler from/to/reply
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Email
{
	/** @var string Name from/to/reply */
	private $name = '';

	/** @var string Email from/to/reply */
	private $email = '';

	/**
	 * Email constructor.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return void
	 */
	public function __construct(string $email, string $name = '')
	{
		$this->setEmail($email);
		$this->setName($name);
	}

	/**
	 * SETTER email
	 *
	 * @param string $email
	 * @return $this
	 */
	public function setEmail(string $email): Email
	{
		$this->email = strtolower(filter_var($email, FILTER_VALIDATE_EMAIL));
		return $this;
	}

	/**
	 * SETTER name
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): Email
	{
		$this->name = (string) filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		return $this;
	}

	/**
	 * GET formated data
	 *
	 * @return array
	 */
	public function get(): array
	{
		$arr = [];
		if($this->email) {
			$arr['Email'] = $this->email;
		}
		else {
			return [];
		}
		if($this->name) {
			$arr['Name'] = $this->name;
		}
		return $arr;
	}

	/**
	 * GETTER name
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * GETTER email
	 *
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function str(): string
	{
		return trim("{$this->getName()} <{$this->getEmail()}>");
	}
}