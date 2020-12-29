<?php
namespace Coercive\Utility\Email\Handler;

use Coercive\Utility\Email\Internal\Status;

/**
 * Interface for email handlers
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
interface InterfaceHandler
{
	/**
	 * Prepare the mailing with injected parameters
	 *
	 * @return void
	 */
	public function prepare();

	/**
	 * Send parametered mailing
	 *
	 * @return void
	 */
	public function send();

	/**
	 * Return current handler status
	 *
	 * @return Status
	 */
	public function getStatus(): Status;
}