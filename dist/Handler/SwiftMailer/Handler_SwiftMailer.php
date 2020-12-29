<?php
namespace Coercive\Utility\Email\Handler\SwiftMailer;

use Exception;
use Swift_Image;
use Swift_Mailer;
use Swift_Message;
use Swift_Attachment;
use Swift_SmtpTransport;
use Swift_SendmailTransport;
use Coercive\Utility\Email\Internal\Param;
use Coercive\Utility\Email\Internal\Status;
use Coercive\Utility\Email\Handler\InterfaceHandler;

/**
 * Utilise Symfony Swift Mailer
 *
 * @link Site https://swiftmailer.symfony.com
 * @link GitHub https://github.com/swiftmailer/swiftmailer
 * @link Documentation https://symfony.com/doc/current/email.html
 * @link Documentation https://swiftmailer.symfony.com/docs/sending.html
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Handler_SwiftMailer implements InterfaceHandler
{
	/** @var Swift_Mailer */
	private $mailer;

	/** @var Swift_SendmailTransport */
	private $transport;

	/** @var Swift_Message */
	private $message;

	/** @var SwiftMailerConfig */
	private $config;

	/** @var Param */
	private $param;

	/** @var Status */
	private $status;

	/**
	 * Handler_SwiftMailer constructor.
	 *
	 * @param SwiftMailerConfig $config
	 * @param Param $param
	 * @return void
	 */
	public function __construct(SwiftMailerConfig $config, Param $param)
	{
		$this->status = new Status;
		$this->config = $config;
		$this->param = $param;
		try {
			$host = $config->getHost();
			$port = $config->getPort();
			$secure = $config->getSecure();
			if($host || $port || $secure) {
				$this->transport = Swift_SmtpTransport::newInstance($host ?: 'localhost', $port ?: 25, $secure ?: null);
				if(($u = $config->getUsername()) && ($p = $config->getPassword())) {
					$this->transport
						->setUsername($u)
						->setPassword($p);
				}
			}
			else {
				$this->transport = Swift_SendmailTransport::newInstance();
			}

			$via = 'localhost';
			if($host || $port) {
				$via = $port ? "[$port] " : '';
				$via .= $host ? $host : '';
			}
			$param->setVia($param->getVia() . ' - ' . $via);

			$this->mailer = Swift_Mailer::newInstance($this->transport);
			$this->message = Swift_Message::newInstance();
			$this->message->setCharset($param->getCharset());
		}
		catch (Exception $e) {
			$this->status->setStatus(false);
			$this->status->setMessage($e->getMessage());
		}
	}

	/**
	 * @inheritDoc
	 * @see InterfaceHandler::prepare()
	 */
	public function prepare()
	{
		try {
			# FROM
			if($from = $this->param->getFrom()) {
				$email = $from->getEmail();
				$name = $from->getName();
				if($name) {
					$this->message->setFrom([$email => $name]);
				}
				else {
					$this->message->setFrom([$email]);
				}
			}

			# TO
			$to = [];
			foreach ($this->param->getRecipients() as $e) {
				$to[$e->getEmail()] = $e->getName();
			}
			if($to) {
				$this->message->setTo($to);
			}

			# REPLY TO
			$reply = [];
			foreach ($this->param->getReplies() as $e) {
				$reply[$e->getEmail()] = $e->getName();
			}
			if($reply) {
				$this->message->setReplyTo($reply);
			}

			# COPY
			$cc = [];
			foreach ($this->param->getCopies() as $e) {
				$cc[$e->getEmail()] = $e->getName();
			}
			if($cc) {
				$this->message->setCc($cc);
			}

			# BLIND
			$bcc = [];
			foreach ($this->param->getBlinds() as $e) {
				$bcc[$e->getEmail()] = $e->getName();
			}
			if($cc) {
				$this->message->setBcc($bcc);
			}

			# VARIABLES
			if($variables = $this->param->getVariables()) {
				$html = $this->param->getHtml();
				foreach ($variables as $key => $value) {
					$html = str_replace("{{var:$key}}", $value, $html);
				}
				$this->param->setHtml($html);
			}

			# ATTACHMENT
			foreach ($this->param->getAttachments() as $attachment) {
				$swiftAttachment = new Swift_Attachment($attachment->getName(), $attachment->getName(), $attachment->getMimeType());
				$this->message->attach($swiftAttachment);
			}

			# EMBED
			foreach ($this->param->getEmbeds() as $embed) {
				$image = new Swift_Image($embed->getBase64Content(), $embed->getName(), $embed->getMimeType());
				$id = $this->message->embed($image);
				if ($html = $this->param->getHtml()) {
					$newHtml = str_replace($embed->getId(), $id, $html);
					$this->param->setHtml($newHtml);
				}
			}

			# DATA
			$this->message->setSubject($this->param->getSubject());
			if ($html = $this->param->getHtml()) {
				$this->message->setBody($html, 'text/html');
			}
			if ($text = $this->param->getText()) {
				$this->message->addPart($text, 'text/plain');
			}
		}
		catch (Exception $e) {
			$this->status->setStatus(false);
			$this->status->setMessage($e->getMessage());
		}
	}

	/**
	 * @inheritDoc
	 * @see InterfaceHandler::prepare()
	 */
	public function send()
	{
		try {
			$nb = $this->mailer->send($this->message);
			$this->status->setStatus($nb > 0);
			if (!$nb) {
				$this->status->setStatus(false);
				$this->status->setMessage('Error : no email has been sent.');
			}
		}
		catch (Exception $e) {
			$this->status->setStatus(false);
			$this->status->setMessage($e->getMessage());
		}
	}

	/**
	 * @inheritDoc
	 * @see InterfaceHandler::getStatus()
	 */
	public function getStatus(): Status
	{
		return $this->status;
	}

	/**
	 * Expose Swift_Mailer
	 *
	 * @return Swift_Mailer
	 */
	public function Swift_Mailer(): Swift_Mailer
	{
		return $this->mailer;
	}

	/**
	 * Expose Swift_SendmailTransport
	 *
	 * @return Swift_SendmailTransport
	 */
	public function Swift_SendmailTransport(): Swift_SendmailTransport
	{
		return $this->transport;
	}

	/**
	 * Expose Swift_Message
	 *
	 * @return Swift_Message
	 */
	public function Swift_Message(): Swift_Message
	{
		return $this->message;
	}
}