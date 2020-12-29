<?php
namespace Coercive\Utility\Email\Internal;

use Exception;
use Symfony\Component\Yaml\Parser;
use Coercive\Utility\Email\Handler\Mailjet\MailjetConfig;
use Coercive\Utility\Email\Handler\PHPMailer\PHPMailerConfig;
use Coercive\Utility\Email\Handler\SwiftMailer\SwiftMailerConfig;

/**
 * Config Mailer
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Config
{
	/** @var Email */
	private $from = null;

	/** @var Email[] */
	private $webmaster = [];

	/** @var Email[] */
	private $to = [];

	/** @var Email[] */
	private $reply = [];

	/** @var Email[] */
	private $copy = [];

	/** @var Email[] */
	private $blind = [];

	/** @var MailjetConfig */
	private $MailjetConfig = null;

	/** @var PHPMailerConfig */
	private $PHPMailerConfig = null;

	/** @var SwiftMailerConfig */
	private $SwiftMailerConfig = null;

	/**
	 * Load config from yaml file
	 *
	 * @param string $path
	 * @return $this
	 * @throws Exception
	 */
	public function load(string $path): Config
	{
		if(!$path || !is_file($path) || !is_readable($path)) {
			throw new Exception('Yaml files not found or not readable : ' . $path);
		}

		$parser = new Parser;
		$yaml = $parser->parse(@file_get_contents($path));
		if(!$yaml || !is_array($yaml)) {
			throw new Exception('Yaml config empty or malformed.');
		}

		if($arr = $yaml['from'] ?? []) {
			$this->setFrom($arr['email'] ?? '', $arr['name'] ?? '');
		}

		if($arr = $yaml['webmaster'] ?? []) {
			foreach ($arr as $a) {
				$this->addWebmaster($a['email'] ?? '', $a['name'] ?? '');
			}
		}

		if($arr = $yaml['to'] ?? []) {
			foreach ($arr as $a) {
				$this->addTo($a['email'] ?? '', $a['name'] ?? '');
			}
		}

		if($arr = $yaml['reply'] ?? []) {
			foreach ($arr as $a) {
				$this->addReply($a['email'] ?? '', $a['name'] ?? '');
			}
		}

		if($arr = $yaml['copy'] ?? []) {
			foreach ($arr as $a) {
				$this->addCopy($a['email'] ?? '', $a['name'] ?? '');
			}
		}

		if($arr = $yaml['blind'] ?? []) {
			foreach ($arr as $a) {
				$this->addBlind($a['email'] ?? '', $a['name'] ?? '');
			}
		}

		if($arr = $yaml['mailjet'] ?? []) {
			if($x = $arr['public'] ?? '') {
				$this->MailjetConfig()->setPublicKey($x);
			}
			if($x = $arr['private'] ?? '') {
				$this->MailjetConfig()->setPrivateKey($x);
			}
			if($x = $arr['version'] ?? '') {
				$this->MailjetConfig()->setVersion($x);
			}
			if($x = boolval($arr['sandbox'] ?? false)) {
				$this->MailjetConfig()->setSandbox($x);
			}
		}

		if($arr = $yaml['phpmailer'] ?? []) {
			if($x = boolval($arr['debug'] ?? false)) {
				$this->PHPMailerConfig()->setDebug($x);
			}
			if($x = $arr['host'] ?? '') {
				$this->PHPMailerConfig()->setHost($x);
			}
			if($x = intval($arr['port'] ?? 0)) {
				$this->PHPMailerConfig()->setPort($x);
			}
			if($x = $arr['username'] ?? '') {
				$this->PHPMailerConfig()->setUsername($x);
			}
			if($x = $arr['password'] ?? '') {
				$this->PHPMailerConfig()->setPassword($x);
			}
		}

		if($arr = $yaml['swiftmailer'] ?? []) {
			if($x = $arr['host'] ?? '') {
				$this->SwiftMailerConfig()->setHost($x);
			}
			if($x = intval($arr['port'] ?? 0)) {
				$this->SwiftMailerConfig()->setPort($x);
			}
			if($x = $arr['username'] ?? '') {
				$this->SwiftMailerConfig()->setUsername($x);
			}
			if($x = $arr['password'] ?? '') {
				$this->SwiftMailerConfig()->setPassword($x);
			}
		}

		return $this;
	}

	/**
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function setFrom(string $email, string $name = ''): Config
	{
		$this->from = new Email($email, $name);
		return $this;
	}

	/**
	 * @return Email|null
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addWebmaster(string $email, string $name = ''): Config
	{
		$this->webmaster[] = new Email($email, $name);
		return $this;
	}

	/**
	 * @return Email[]
	 */
	public function getWebmaster(): array
	{
		return $this->webmaster;
	}

	/**
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addTo(string $email, string $name = ''): Config
	{
		$this->to[] = new Email($email, $name);
		return $this;
	}

	/**
	 * @return Email[]
	 */
	public function getTo(): array
	{
		return $this->to;
	}

	/**
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addReply(string $email, string $name = ''): Config
	{
		$this->reply[] = new Email($email, $name);
		return $this;
	}

	/**
	 * @return Email[]
	 */
	public function getReply(): array
	{
		return $this->reply;
	}

	/**
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addCopy(string $email, string $name = ''): Config
	{
		$this->copy[] = new Email($email, $name);
		return $this;
	}

	/**
	 * @return Email[]
	 */
	public function getCopy(): array
	{
		return $this->copy;
	}

	/**
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addBlind(string $email, string $name = ''): Config
	{
		$this->blind[] = new Email($email, $name);
		return $this;
	}

	/**
	 * @return Email[]
	 */
	public function getBlind(): array
	{
		return $this->blind;
	}

	/**
	 * @return MailjetConfig
	 */
	public function MailjetConfig()
	{
		if(null === $this->MailjetConfig) {
			$this->MailjetConfig = new MailjetConfig;
		}
		return $this->MailjetConfig;
	}

	/**
	 * @return PHPMailerConfig
	 */
	public function PHPMailerConfig()
	{
		if(null === $this->PHPMailerConfig) {
			$this->PHPMailerConfig = new PHPMailerConfig;
		}
		return $this->PHPMailerConfig;
	}

	/**
	 * @return SwiftMailerConfig
	 */
	public function SwiftMailerConfig()
	{
		if(null === $this->SwiftMailerConfig) {
			$this->SwiftMailerConfig = new SwiftMailerConfig;
		}
		return $this->SwiftMailerConfig;
	}
}