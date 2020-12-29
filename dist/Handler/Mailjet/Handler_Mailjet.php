<?php
namespace Coercive\Utility\Email\Handler\Mailjet;

use Exception;
use Mailjet\Client;
use Mailjet\Resources;
use Coercive\Utility\Email\Internal\Param;
use Coercive\Utility\Email\Internal\Status;
use Coercive\Utility\Email\Handler\InterfaceHandler;

/**
 * Use Mailjet
 *
 * @link Site https://fr.mailjet.com/
 * @link GitHub https://github.com/mailjet/mailjet-apiv3-php
 * @link Documentation https://dev.mailjet.com/email/guides/getting-started/
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Handler_Mailjet implements InterfaceHandler
{
	/** @var Client */
	private $Mailjet;

	/** @var MailjetConfig */
	private $config;

	/** @var Param */
	private $param;

	/** @var Status */
	private $status;

	/**
	 * Exposed in public for custom injection
	 * @var array
	 */
	public $body = [];

	/**
	 * Handler_Mailjet constructor.
	 *
	 * @param MailjetConfig $config
	 * @param Param $param
	 * @return void
	 */
	public function __construct(MailjetConfig $config, Param $param)
	{
		$this->status = new Status;
		$this->config = $config;
		$this->param = $param;
		if($config->getSandbox()) {
			$param->setVia($param->getVia() . ' SANDBOX');
		}
		try {
			$this->Mailjet = new Client($config->getPublicKey(), $config->getPrivateKey(), true, [
				'version' => $config->getVersion()
			]);
		}
		catch (Exception $e) {
			$this->status->setStatus(false);
			$this->status->setMessage($e->getMessage());
		}
	}

	/**
	 * Prepare on bulk param
	 *
	 * @param Param $param
	 * @return array
	 */
	static public function format(Param $param): array
	{
		$message = [];

		# CUSTOM ID
		if($id = $param->getId()) {
			$message['CustomID'] = $id;
		}

		# EVENT PAYLOAD
		if($payload = $param->getPayload()) {
			$message['EventPayload'] = $payload;
		}

		# CUSTOM CAMPAIGN
		if($campaign = $param->getCampaign()) {
			$message['CustomCampaign'] = $payload;
			if($deduplicate = $param->getDeduplicate()) {
				$message['DeduplicateCampaign'] = $deduplicate;
			}
		}

		# URL TAGS
		if($urltags = $param->getUrlTags()) {
			$message['URLTags'] = $urltags;
		}

		# FROM
		$from = [];
		if($f = $param->getFrom()) {
			if($email = $f->getEmail()) {
				$from['Email'] = $email;
			}
			if($name = $f->getName()) {
				$from['Name'] = $name;
			}
		}
		if($from) {
			$message['From'] = $from;
		}

		# TO
		$to = []; $i = 0;
		foreach ($param->getRecipients() as $e) {
			$to[$i]['Email'] = $e->getEmail();
			if($name = $e->getName()) {
				$to[$i]['Name'] = $name;
			}
			$i++;
		}
		if($to) {
			$message['To'] = $to;
		}

		# REPLY TO
		$reply = []; $i = 0;
		foreach ($param->getReplies() as $e) {
			$reply[$i]['Email'] = $e->getEmail();
			if($name = $e->getName()) {
				$reply[$i]['Name'] = $name;
			}
			$i++;
		}
		if($reply) {
			$message['ReplyTo'] = $reply;
		}

		# COPY cc
		$copy = []; $i = 0;
		foreach ($param->getCopies() as $e) {
			$copy[$i]['Email'] = $e->getEmail();
			if($name = $e->getName()) {
				$copy[$i]['Name'] = $name;
			}
			$i++;
		}
		if($copy) {
			$message['Cc'] = $copy;
		}

		# BLIND cc
		$blind = []; $i = 0;
		foreach ($param->getBlinds() as $e) {
			$blind[$i]['Email'] = $e->getEmail();
			if($name = $e->getName()) {
				$blind[$i]['Name'] = $name;
			}
			$i++;
		}
		if($blind) {
			$message['Bcc'] = $blind;
		}

		# TEMPLATE ID
		if($id = $param->getTemplateId()) {
			$message['TemplateID'] = $id;
			$message['TemplateLanguage'] = true;
		}

		# VARIABLES
		if($list = $param->getVariables()) {
			$message['Variables'] = $list;
		}
		if(isset($message['Variables'])) {
			$message['TemplateLanguage'] = true;
		}

		# ATTACHMENT
		$attachments = [];
		foreach ($param->getAttachments() as $attachment) {
			$attachments[] = $attachment->get();
		}
		if($attachments) {
			$message['Attachments'] = $attachments;
		}

		# EMBED
		$embeds = [];
		foreach ($param->getEmbeds() as $embed) {
			$embeds[] = $embed->get();
		}
		if($embeds) {
			$message['InlinedAttachments'] = $attachments;
		}

		# DATA
		if ($subject = $param->getSubject()) {
			$message['Subject'] = $subject;
		}
		if ($html = $param->getHtml()) {
			$message['HTMLPart'] = $html;
		}
		if ($text = $param->getText()) {
			$message['TextPart'] = $text;
		}

		return $message;
	}

	/**
	 * @inheritDoc
	 * @see InterfaceHandler::prepare()
	 */
	public function prepare()
	{
		try {
			$this->body = [];
			$message = Handler_Mailjet::format($this->param);

			$this->body['Messages'][] = $message;
			$this->body['SandboxMode'] = $this->config->getSandbox();
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
			$response = $this->Mailjet->post(Resources::$Email, [
				'body' => $this->body
			]);

			$data = $response->getData();
			$status = $response->success();

			$this->status->setStatus($status);
			if($status && $this->config->getSandbox()) {
				$this->status->addDebug(json_encode($data));
			}
			if (!$status) {
				$this->status->setCode($data['ErrorCode'] ?? '');
				$this->status->setMessage($response->getReasonPhrase() . ' | ' .  ($data['ErrorMessage'] ?? ''));
				$this->status->setTrace(json_encode($data));
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
	 * Expose Mailjet
	 *
	 * @return Client
	 */
	public function Mailjet(): Client
	{
		return $this->Mailjet;
	}
}