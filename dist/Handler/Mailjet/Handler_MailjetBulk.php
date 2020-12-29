<?php
namespace Coercive\Utility\Email\Handler\Mailjet;

use Exception;
use Mailjet\Client;
use Mailjet\Resources;
use Coercive\Utility\Email\Internal\Bulk;
use Coercive\Utility\Email\Internal\Status;
use Coercive\Utility\Email\Handler\InterfaceHandler;

/**
 * Utilise Mailjet : mode bulk
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
class Handler_MailjetBulk implements InterfaceHandler
{
	/** @var Client */
	private $Mailjet;

	/** @var MailjetConfig */
	private $config;

	/** @var Bulk */
	private $bulk;

	/** @var Status */
	private $status;

	/**
	 * Exposed in public for custom injection
	 * @var array
	 */
	public $body = [];

	/**
	 * Handler_MailjetBulk constructor.
	 *
	 * @param MailjetConfig $config
	 * @param Bulk $bulk
	 * @return void
	 */
	public function __construct(MailjetConfig $config, Bulk $bulk)
	{
		$this->status = new Status;
		$this->config = $config;
		$this->bulk = $bulk;
		if($config->getSandbox()) {
			$bulk->getGlobal()->setVia($bulk->getGlobal()->getVia() . ' SANDBOX');
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
	 * @inheritDoc
	 * @see InterfaceHandler::prepare()
	 */
	public function prepare()
	{
		try {
			$this->body = [];
			if($global = $this->bulk->getGlobal()) {
				$this->body['Globals'] = Handler_Mailjet::format($global);
			}
			foreach ($this->bulk->getBulk() as $param) {
				$this->body['Messages'][] = Handler_Mailjet::format($param);
			}
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
				$this->status->setMessage((string) $response->getReasonPhrase());
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