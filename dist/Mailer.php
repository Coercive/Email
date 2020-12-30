<?php
namespace Coercive\Utility\Email;

use Exception;
use Coercive\Utility\Email\Internal\Bulk;
use Coercive\Utility\Email\Internal\Param;
use Coercive\Utility\Email\Internal\Config;
use Coercive\Utility\Email\Internal\Status;
use Coercive\Utility\Email\Internal\Monitoring;
use Coercive\Utility\Email\Handler\InterfaceHandler;
use Coercive\Utility\Email\Handler\Mailjet\Handler_Mailjet;
use Coercive\Utility\Email\Handler\Mailjet\Handler_MailjetBulk;
use Coercive\Utility\Email\Handler\PHPMailer\Handler_PHPMailer;
use Coercive\Utility\Email\Handler\SwiftMailer\Handler_SwiftMailer;

/**
 * Class Mailer
 *
 * Verba volant, scripta manent.
 *
 * @package Coercive\Utility\Email
 * @author Anthony <contact@coercive.fr>
 * @copyright 2021 Anthony Moral
 * @license MIT
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Mailer
{
	const DEFAULT_LANGUAGE = 'en';

	const
		MODE_TEST = 'TEST',
		MODE_LIVE = 'LIVE',
		MODES = [
			self::MODE_TEST,
			self::MODE_LIVE
		];

	const
		HANDLER_MODE_PHPMAILER = 'PHPMAILER',
		HANDLER_MODE_SWIFTMAILER = 'SWIFTMAILER',
		HANDLER_MODE_MAILJET = 'MAILJET',
		HANDLER_MODE_MAILJETBULK = 'MAILJETBULK',
		HANDLER_MODES = [
			self::HANDLER_MODE_PHPMAILER,
			self::HANDLER_MODE_SWIFTMAILER,
			self::HANDLER_MODE_MAILJETBULK,
			self::HANDLER_MODE_MAILJET
		];

	/** @var Config */
	private $config;

	/** @var Bulk */
	private $bulk;

	/** @var string Current mode */
	private $mode = self::MODE_TEST;

	/** @var string Current handler mode */
	private $handlerMode = self::HANDLER_MODE_PHPMAILER;

	/** @var string Default language */
	private $defaultLanguage = '';

	/** @var string Default language path */
	private $defaultLanguagePath = '';

	/** @var Monitoring */
	protected $monitoring = null;

	/** @var Param */
	protected $param = null;

	/** @var Handler_PHPMailer|Handler_SwiftMailer|Handler_Mailjet|Handler_MailjetBulk|InterfaceHandler */
	protected $handler = null;

	/** @var Status */
	protected $lastHandlerStatus = null;

	/** @var bool Email system enabled / disabled */
	private $status = false;

	/** @var int Attachment max size (default : 10 Mb) */
	private $maxSize = 10485760;

	/** @var int Current attachments file size amount */
	private $totalSize = 0;

	/**
	 * Check if is in test mode
	 *
	 * @return bool
	 */
	private function isTest(): bool
	{
		return self::MODE_TEST === $this->mode;
	}

	/**
	 * Check if is in live mode
	 *
	 * @return bool
	 */
	private function isLive(): bool
	{
		return self::MODE_LIVE === $this->mode;
	}

	/**
	 * Email constructor.
	 *
	 * @param Config $config
	 * @return void
	 * @throws Exception
	 */
	public function __construct(Config $config)
	{
		/** @var config */
		$this->config = $config;

		# Init monitoring
		$this->monitoring = new Monitoring;

		# First initialize
		$this->clearBulk();
		$this->init();
	}

	/**
	 * Export Config
	 *
	 * @return Config
	 */
	public function Config(): Config
	{
		return $this->config;
	}

	/**
	 * Export Param
	 *
	 * @return Param
	 */
	public function Param(): Param
	{
		return $this->param;
	}

	/**
	 * Export Handler
	 *
	 * @return Handler_PHPMailer|Handler_SwiftMailer|Handler_Mailjet|Handler_MailjetBulk|InterfaceHandler
	 */
	public function Handler(): InterfaceHandler
	{
		return $this->handler;
	}

	/**
	 * Export Monitoring
	 *
	 * @return Monitoring
	 */
	public function Monitoring(): Monitoring
	{
		return $this->monitoring;
	}

	/**
	 * Set the mode property.
	 * - TEST : webmaster emails are setted from config.
	 * - LIVE : real emails are setted.
	 *
	 * @param string $mode [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function setMode(string $mode = self::MODE_TEST): Mailer
	{
		if(!in_array($mode, self::MODES, true)) {
			throw new Exception("Mode is not allowed : $mode, must be " . implode(' | ', self::MODES));
		}
		$this->mode = $mode;
		return $this;
	}

	/**
	 * Mode TEST : webmaster emails are setted from config.
	 *
	 * @return $this
	 */
	public function setModeTest(): Mailer
	{
		$this->mode = self::MODE_TEST;
		return $this;
	}

	/**
	 * Mode LIVE : real emails are setted.
	 *
	 * @return $this
	 */
	public function setModeLive(): Mailer
	{
		$this->mode = self::MODE_LIVE;
		return $this;
	}

	/**
	 * Enable/disable real send mail.
	 * - Enable : the send part will be used and the monitoring too.
	 * - Disable : only the monitoring is used.
	 *
	 * @warning Mailjet : you can check the sent data with set enable status and enable sandbox from the config.
	 *
	 * @param bool $status
	 * @return $this
	 */
	public function setStatus(bool $status): Mailer
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Enable real send mail, the send part will be used and the monitoring too.
	 *
	 * @return $this
	 */
	public function enable(): Mailer
	{
		$this->status = true;
		return $this;
	}

	/**
	 * Disable real send mail, only the monitoring is used.
	 *
	 * @return $this
	 */
	public function disable(): Mailer
	{
		$this->status = false;
		return $this;
	}

	/**
	 * Set the handler mode property.
	 *
	 * @param string $handler [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function setHandlerMode(string $handler = self::HANDLER_MODE_PHPMAILER): Mailer
	{
		if(!in_array($handler, self::HANDLER_MODES, true)) {
			throw new Exception("Handler mode is not allowed : $handler, must be " . implode(' | ', self::HANDLER_MODES));
		}
		$this->handlerMode = $handler;
		return $this;
	}

	/**
	 * Handler mode : PHPMailer
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function usePHPMailer(): Mailer
	{
		$this->setHandlerMode(self::HANDLER_MODE_PHPMAILER);
		return $this;
	}

	/**
	 * Handler mode : Symfony Swift Mailer
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function useSwiftMailer(): Mailer
	{
		$this->setHandlerMode(self::HANDLER_MODE_SWIFTMAILER);
		return $this;
	}

	/**
	 * Handler mode : Mailjet
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function useMailjet(): Mailer
	{
		$this->setHandlerMode(self::HANDLER_MODE_MAILJET);
		return $this;
	}

	/**
	 * Handler mode : Mailjet Bulk
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function useMailjetBulk(): Mailer
	{
		$this->setHandlerMode(self::HANDLER_MODE_MAILJETBULK);
		return $this;
	}

	/**
	 * Set the default language property.
	 *
	 * @param string $lang
	 * @return $this
	 * @throws Exception
	 */
	public function setDefaultLanguage(string $lang = self::DEFAULT_LANGUAGE): Mailer
	{
		$this->defaultLanguage = $lang;
		return $this;
	}

	/**
	 * Set the default language path property.
	 *
	 * @param string $path
	 * @return $this
	 * @throws Exception
	 */
	public function setDefaultLanguagePath(string $path): Mailer
	{
		$this->defaultLanguagePath = $path;
		return $this;
	}

	/**
	 * Last sent status.
	 *
	 * @return Status
	 */
	public function getLastSentStatus(): Status
	{
		return $this->lastHandlerStatus ?: new Status;
	}

	/**
	 * Attachments max size allowed.
	 *
	 * @param int $megaBytes
	 * @return $this
	 */
	public function setFileMaxSize(int $megaBytes): Mailer
	{
		$this->maxSize = $megaBytes * 1024 * 1024;
		return $this;
	}

	/**
	 * Prepare email / bulk.
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function prepare(): Mailer
	{
		# Reset handler
		$this->handler = null;

		# Create Monitoring Status
		switch ($this->handlerMode) {
			case self::HANDLER_MODE_PHPMAILER:
			case self::HANDLER_MODE_SWIFTMAILER:
			case self::HANDLER_MODE_MAILJET:
				$this->param->setVia($this->handlerMode);
				$this->Monitoring()->create($this->param);
				break;
			case self::HANDLER_MODE_MAILJETBULK:
				if($global = $this->bulk->getGlobal()) {
					$global->setVia($this->handlerMode);
					$this->Monitoring()->create($global);
				}
				break;
			default:
				throw new Exception('Undefined mail hander');
		}

		# [ONLY IF ENABLE] > Prepare mail
		if($this->status) {

			switch ($this->handlerMode) {
				case self::HANDLER_MODE_PHPMAILER:
					$this->handler = new Handler_PHPMailer($this->config->PHPMailerConfig(), $this->param);
					break;
				case self::HANDLER_MODE_SWIFTMAILER:
					$this->handler = new Handler_SwiftMailer($this->config->SwiftMailerConfig(), $this->param);
					break;
				case self::HANDLER_MODE_MAILJET:
					$this->handler = new Handler_Mailjet($this->config->MailjetConfig(), $this->param);
					break;
				case self::HANDLER_MODE_MAILJETBULK:
					$this->handler = new Handler_MailjetBulk($this->config->MailjetConfig(), $this->bulk);
					break;
				default:
					throw new Exception('Undefined mail hander');
			}

			$this->handler->prepare();
		}

		return $this;
	}

	/**
	 * Send email / bulk.
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function send(): Mailer
	{
		# Reset last status
		$this->lastHandlerStatus = null;

		# [ONLY IF ENABLE] > Send mail
		if($this->status && $this->handler) {

			$this->handler->send();
			$this->lastHandlerStatus = $this->handler->getStatus();

			# Update monitoring validation
			switch ($this->handlerMode) {
				case self::HANDLER_MODE_PHPMAILER:
				case self::HANDLER_MODE_SWIFTMAILER:
				case self::HANDLER_MODE_MAILJET:
					$this->Monitoring()->update($this->lastHandlerStatus, $this->param);
					break;
				case self::HANDLER_MODE_MAILJETBULK:
					if($global = $this->bulk->getGlobal()) {
						$this->Monitoring()->update($this->lastHandlerStatus, $global);
					}
					break;
				default:
					throw new Exception('Undefined mail hander');
			}
		}

		# [UNPREPARED]
		elseif($this->status && !$this->handler) {
			$this->lastHandlerStatus = (new Status)
				->setStatus(false)
				->setMessage('Mail handler is not prepared correctly.');
		}

		# [DISABLED] > Do NOT send mail > status is ok
		else {
			$this->lastHandlerStatus = (new Status)
				->setStatus(true)
				->setMessage('[DISABLE] The email is not sent.');
		}
		return $this;
	}

	/**
	 * (re)Initialize
	 *
	 * @param bool $from [optional]
	 * @return $this
	 */
	public function init(bool $from = false): Mailer
	{
		$this->param = new Param;
		if($this->defaultLanguage) {
			$this->param->setLang($this->defaultLanguage);
		}
		if($this->defaultLanguagePath) {
			$this->param->setLangPath($this->defaultLanguagePath);
		}
		if($from && ($f = $this->config->getFrom())) {
			$this->param->setFrom($f->getEmail(), $f->getName());
		}
		return $this;
	}

	/**
	 * Add current param to bulk.
	 *
	 * @param bool $global
	 * @return $this
	 */
	public function bulk(bool $global = false): Mailer
	{
		if($global) {
			$this->bulk->setGlobal($this->param);
		}
		else {
			$this->bulk->addBulk($this->param);
		}
		$this->param = null;
		return $this;
	}

	/**
	 * Create new empty bulk.
	 *
	 * @param bool $global [optional]
	 * @return $this
	 */
	public function clearBulk(bool $global = false): Mailer
	{
		$bulk = new Bulk;
		if($global && ($g = $this->bulk->getGlobal())) {
			$bulk->setGlobal($g);
		}
		$this->bulk = $bulk;
		return $this;
	}

	/**
	 * Set the language parameter.
	 *
	 * Used in PHPMailer config.
	 *
	 * @param string $lang
	 * @return $this
	 * @throws Exception
	 */
	public function setLang(string $lang): Mailer
	{
		$this->param->setLang($lang);
		return $this;
	}

	/**
	 * Set the Language path parameter.
	 *
	 * Used in PHPMailer config.
	 *
	 * @param string $path
	 * @return $this
	 * @throws Exception
	 */
	public function setLangPath(string $path): Mailer
	{
		$this->param->setLangPath($path);
		return $this;
	}

	/**
	 * Set the charset parameter.
	 *
	 * Used in PHPMailer config.
	 *
	 * @param string $charset
	 * @return $this
	 * @throws Exception
	 */
	public function setCharset(string $charset): Mailer
	{
		$this->param->setCharset($charset);
		return $this;
	}

	/**
	 * Set from address.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function setFrom(string $email, string $name = ''): Mailer
	{
		if($this->isTest()) {
			if($from = $this->config->getFrom()) {
				$email = $from->getEmail();
				$name = $from->getName();
			}
			else {
				$email = '';
				$name = '';
			}
		}
		$this->param->setFrom($email, $name);
		return $this;
	}

	/**
	 * Add recipient addresses (To).
	 *
	 * @param array $addresses [ [name,email] ] or [ [email] ]
	 * @return $this
	 * @throws Exception
	 */
	public function addRecipients(array $addresses): Mailer
	{
		foreach ($addresses as $address) {
			if(is_string($address)) {
				$this->addRecipient($address);
			}
			elseif(is_array($address)) {
				$email = strval($address['email'] ?? '');
				$name = strval($address['name'] ?? '');
				if(!$email) {
					throw new Exception('Email key not found in array.');
				}
				$this->addRecipient($email, $name);
			}
			else {
				throw new Exception('The address type is not supported.');
			}
		}
		return $this;
	}

	/**
	 * Add recipient address (To).
	 *
	 * @param string $email [optional]
	 * @param string $name [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function addRecipient(string $email = '', string $name = ''): Mailer
	{
		if($this->isTest()) {
			foreach ($this->config->getWebmaster() as $e) {
				$this->param->addRecipient($e->getEmail(), $e->getName());
			}
		}
		elseif(!$email) {
			foreach ($this->config->getTo() as $e) {
				$this->param->addRecipient($e->getEmail(), $e->getName());
			}
		}
		else {
			$this->param->addRecipient($email, $name);
		}
		return $this;
	}

	/**
	 * Clear recipient addresses (To).
	 *
	 * @return $this
	 */
	public function clearRecipients(): Mailer
	{
		$this->param->clearRecipients();
		return $this;
	}

	/**
	 * Add copy addresses (Cc).
	 *
	 * @param array $addresses [ [name,email] ] or [ [email] ]
	 * @return $this
	 * @throws Exception
	 */
	public function addCopies(array $addresses): Mailer
	{
		foreach ($addresses as $address) {
			if(is_string($address)) {
				$this->addCopy($address);
			}
			elseif(is_array($address)) {
				$email = strval($address['email'] ?? '');
				$name = strval($address['name'] ?? '');
				if(!$email) {
					throw new Exception('Email key not found in array.');
				}
				$this->addCopy($email, $name);
			}
			else {
				throw new Exception('The address type is not supported.');
			}
		}
		return $this;
	}

	/**
	 * Add copy address (Cc).
	 *
	 * @param string $email [optional]
	 * @param string $name [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function addCopy(string $email = '', string $name = ''): Mailer
	{
		if($this->isTest()) {
			foreach ($this->config->getWebmaster() as $e) {
				$this->param->addCopy($e->getEmail(), $e->getName());
			}
		}
		elseif(!$email) {
			foreach ($this->config->getCopy() as $e) {
				$this->param->addCopy($e->getEmail(), $e->getName());
			}
		}
		else {
			$this->param->addCopy($email, $name);
		}
		return $this;
	}

	/**
	 * Clear copy addresses (Cc).
	 *
	 * @return $this
	 */
	public function clearCopies(): Mailer
	{
		$this->param->clearCopies();
		return $this;
	}

	/**
	 * Add blinds addresses (Cc).
	 *
	 * @param array $addresses [ [name,email] ] or [ [email] ]
	 * @return $this
	 * @throws Exception
	 */
	public function addBlinds(array $addresses): Mailer
	{
		foreach ($addresses as $address) {
			if(is_string($address)) {
				$this->addBlind($address);
			}
			elseif(is_array($address)) {
				$email = strval($address['email'] ?? '');
				$name = strval($address['name'] ?? '');
				if(!$email) {
					throw new Exception('Email key not found in array.');
				}
				$this->addBlind($email, $name);
			}
			else {
				throw new Exception('The address type is not supported.');
			}
		}
		return $this;
	}

	/**
	 * Add blind address (Bcc).
	 *
	 * @param string $email [optional]
	 * @param string $name [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function addBlind(string $email = '', string $name = ''): Mailer
	{
		if($this->isTest()) {
			foreach ($this->config->getWebmaster() as $e) {
				$this->param->addBlind($e->getEmail(), $e->getName());
			}
		}
		elseif(!$email) {
			foreach ($this->config->getBlind() as $e) {
				$this->param->addBlind($e->getEmail(), $e->getName());
			}
		}
		else {
			$this->param->addBlind($email, $name);
		}
		return $this;
	}

	/**
	 * Clear blind addresses (Bcc).
	 *
	 * @return $this
	 */
	public function clearBlinds(): Mailer
	{
		$this->param->clearBlinds();
		return $this;
	}

	/**
	 * Add reply addresses (Cc).
	 *
	 * @param array $addresses [ [name,email] ] or [ [email] ]
	 * @return $this
	 * @throws Exception
	 */
	public function addReplies(array $addresses): Mailer
	{
		foreach ($addresses as $address) {
			if(is_string($address)) {
				$this->addReply($address);
			}
			elseif(is_array($address)) {
				$email = strval($address['email'] ?? '');
				$name = strval($address['name'] ?? '');
				if(!$email) {
					throw new Exception('Email key not found in array.');
				}
				$this->addReply($email, $name);
			}
			else {
				throw new Exception('The address type is not supported.');
			}
		}
		return $this;
	}

	/**
	 * Add reply address (ReplyTo).
	 *
	 * @param string $email [optional]
	 * @param string $name [optional]
	 * @return $this
	 * @throws Exception
	 */
	public function addReply(string $email = '', string $name = ''): Mailer
	{
		if($this->isTest()) {
			foreach ($this->config->getWebmaster() as $e) {
				$this->param->addReply($e->getEmail(), $e->getName());
			}
		}
		elseif(!$email) {
			foreach ($this->config->getReply() as $e) {
				$this->param->addReply($e->getEmail(), $e->getName());
			}
		}
		else {
			$this->param->addReply($email, $name);
		}
		return $this;
	}

	/**
	 * Clear reply addresses (ReplyTo).
	 *
	 * @return $this
	 */
	public function clearReplies(): Mailer
	{
		$this->param->clearReplies();
		return $this;
	}

	/**
	 * Set the email subject.
	 *
	 * @param string $subject
	 * @return $this
	 */
	public function setSubject(string $subject): Mailer
	{
		if($this->isTest()) {
			$subject = "[TEST] $subject";
		}
		$this->param->setSubject($subject);
		return $this;
	}

	/**
	 * Set the body HTML.
	 *
	 * @param string $html
	 * @return $this
	 */
	public function setHtml(string $html): Mailer
	{
		$this->param->setHtml($html);
		return $this;
	}

	/**
	 * Set the body raw text.
	 *
	 * @param string $text
	 * @return $this
	 */
	public function setText(string $text): Mailer
	{
		$this->param->setText($text);
		return $this;
	}

	/**
	 * Add email attachments.
	 *
	 * @param string $path
	 * @param string $name
	 * @return $this
	 * @throws Exception
	 */
	public function addAttachment(string $path, string $name): Mailer
	{
		# File does not exists
		if(!is_file($path) || !($size = filesize($path))) {
			throw new Exception("Can't load attachement file : {$name}[{$path}]");
		}

		# Calc total size
		$this->totalSize += $size;
		if($this->totalSize > $this->maxSize) {
			throw new Exception("The maximum size of attachments has been exceeded : {$this->totalSize}/{$this->maxSize}");
		}

		$this->param->addAttachment($path, $name);
		return $this;
	}

	/**
	 * Clear attachments.
	 *
	 * @return $this
	 */
	public function clearAttachments(): Mailer
	{
		$this->param->clearAttachments();
		return $this;
	}

	/**
	 * Add embed (inline attachment).
	 *
	 * @param string $path
	 * @param string $name
	 * @return $this
	 * @throws Exception
	 */
	public function addEmbed(string $path, string $name): Mailer
	{
		# File does not exists
		if(!is_file($path) || !($size = filesize($path))) {
			throw new Exception("Can't load attachement file : {$name}[{$path}]");
		}

		# Calc total size
		$this->totalSize += $size;
		if($this->totalSize > $this->maxSize) {
			throw new Exception("The maximum size of attachments has been exceeded : {$this->totalSize}/{$this->maxSize}");
		}

		$this->param->addEmbed($path, $name);
		return $this;
	}

	/**
	 * Clear embeds (inline attachment).
	 *
	 * @return $this
	 */
	public function clearEmbeds(): Mailer
	{
		$this->param->clearEmbeds();
		return $this;
	}

	/**
	 * Set the template id.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setTemplateId(string $id): Mailer
	{
		$this->param->setTemplateId($id);
		return $this;
	}

	/**
	 * Add some variables.
	 *
	 * @param string[] $variables
	 * @return $this
	 */
	public function addVariables(array $variables): Mailer
	{
		$this->param->addVariables($variables);
		return $this;
	}

	/**
	 * Add variable.
	 *
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function addVariable(string $key, string $value): Mailer
	{
		$this->param->addVariable($key, $value);
		return $this;
	}

	/**
	 * Clear variables property.
	 *
	 * @return $this
	 */
	public function clearVariables(): Mailer
	{
		$this->param->clearVariables();
		return $this;
	}
}