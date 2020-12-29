<?php
namespace Coercive\Utility\Email\Handler\PHPMailer;

use Exception;
use PHPMailer;
use Coercive\Utility\Email\Internal\Param;
use Coercive\Utility\Email\Internal\Status;
use Coercive\Utility\Email\Handler\InterfaceHandler;

/**
 * Use PHPMailer : PHP email creation and transport class
 *
 * @link GitHub https://github.com/PHPMailer/PHPMailer
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Handler_PHPMailer implements InterfaceHandler
{
	/** @var PHPMailer */
	private $PHPMailer;

	/** @var Status */
	private $status;

	/** @var PHPMailerConfig */
	private $config;

	/** @var Param */
	private $param;

	/**
	 * Handler_PHPMailer constructor.
	 *
	 * @param PHPMailerConfig $config
	 * @param Param $param
	 * @return void
	 */
	public function __construct(PHPMailerConfig $config, Param $param)
	{
		$this->status = new Status;
		$this->config = $config;
		$this->param = $param;
		try {
			$this->PHPMailer = new PHPMailer;
			$this->PHPMailer->CharSet = $param->getCharset();
			$this->PHPMailer->isSMTP();
			if($lang = $param->getLang()) {
				if($path = $param->getLangPath()) {
					$this->PHPMailer->setLanguage(strtolower($lang), $path);
				}
				else {
					$this->PHPMailer->setLanguage(strtolower($lang));
				}
			}

			if($config->getDebug()) {
				$this->PHPMailer->SMTPDebug = 1;
				$this->PHPMailer->Debugoutput = function($str, $level) {
					$this->status->addDebug($str);
				};
			}
			if($port = $config->getPort()) {
				$this->PHPMailer->Port = $port;
			}
			if($host = $config->getHost()) {
				$this->PHPMailer->Host = $host;
			}
			if(($u = $config->getUsername()) && ($p = $config->getPassword())) {
				$this->PHPMailer->SMTPAuth = true;
				$this->PHPMailer->Username = $u;
				$this->PHPMailer->Password = $p;
			}

			$via = 'localhost';
			if($host || $port) {
				$via = $port ? "[$port] " : '';
				$via .= $host ? $host : '';
			}
			$param->setVia($param->getVia() . ' - ' . $via);

			$this->PHPMailer->smtpConnect([
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				]
			]);

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
				$this->PHPMailer->From = $from->getEmail();
				$this->PHPMailer->FromName = $from->getName();
			}

			# TO
			foreach ($this->param->getRecipients() as $e) {
				$this->PHPMailer->addAddress($e->getEmail(), $e->getName());
			}

			# REPLY TO
			foreach ($this->param->getReplies() as $e) {
				$this->PHPMailer->addReplyTo($e->getEmail(), $e->getName());
			}

			# COPY
			foreach ($this->param->getCopies() as $e) {
				$this->PHPMailer->addCC($e->getEmail(), $e->getName());
			}

			# BLIND
			foreach ($this->param->getBlinds() as $e) {
				$this->PHPMailer->addBCC($e->getEmail(), $e->getName());
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
				$this->PHPMailer->addAttachment($attachment->getPath(), $attachment->getName());
			}

			# EMBED
			foreach ($this->param->getEmbeds() as $embed) {
				$this->PHPMailer->addStringEmbeddedImage($embed->getBase64Content(), $embed->getId(), $embed->getName());
			}

			# DATA
			$this->PHPMailer->Subject = $this->param->getSubject();
			if ($this->param->getHtml()) {
				$this->PHPMailer->msgHTML($this->param->getHtml());
				$this->PHPMailer->AltBody = $this->param->getText();
			}
			elseif ($this->param->getText()) {
				$this->PHPMailer->Body = $this->param->getText();
			}
		}
		catch (Exception $e) {
			$this->status->setStatus(false);
			$this->status->setMessage($e->getMessage());
		}
	}

	/**
	 * @inheritDoc
	 * @see InterfaceHandler::send()
	 */
	public function send()
	{
		try {
			$status = $this->PHPMailer->send();
			$this->status->setStatus($status);
			if ($this->PHPMailer->isError()) {
				$this->status->setStatus(false);
				$this->status->setMessage((string) $this->PHPMailer->ErrorInfo);
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
	 * Expose PHPMailer
	 *
	 * @return PHPMailer
	 */
	public function PHPMailer(): PHPMailer
	{
		return $this->PHPMailer;
	}
}