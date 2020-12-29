<?php
namespace Coercive\Utility\Email\Internal;

/**
 * Paramétrage d'un envoi
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Param
{
	/** @var string[] List of all recipients (to/cc/bcc) */
	private $recipients = [];

	/** @var string */
	private $charset = 'UTF-8';

	/** @var string */
	private $lang = '';

	/** @var string */
	private $langpath = '';

	/** @var string */
	private $via = '';

	/** @var string */
	private $id = '';

	/** @var string */
	private $payload = '';

	/** @var string */
	private $campaign = '';

	/** @var bool */
	private $deduplicate = false;

	/** @var string */
	private $urltags = '';

	/** @var Email */
	private $from = null;

	/** @var Email[] */
	private $reply = [];

	/** @var Email[] */
	private $to = [];

	/** @var Email[] */
	private $copy = [];

	/** @var Email[] */
	private $blind = [];

	/** @var string */
	private $subject = '';

	/** @var string */
	private $text = '';

	/** @var string */
	private $html = '';

	/** @var string */
	private $templateId = '';

	/** @var array {key:string} {value:string|string[]} */
	private $variables = [];

	/** @var Attachment[] */
	private $attachment = [];

	/** @var Attachment[] */
	private $embed = [];

	/**
	 * Set the charset property.
	 *
	 * @param string $charset
	 * @return $this
	 */
	public function setCharset(string $charset): Param
	{
		$this->charset = $charset;
		return $this;
	}

	/**
	 * Get the charset property.
	 *
	 * @return string
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Set the lang property.
	 *
	 * @param string $lang
	 * @return $this
	 */
	public function setLang(string $lang): Param
	{
		$this->lang = $lang;
		return $this;
	}

	/**
	 * Get the lang property.
	 *
	 * @return string
	 */
	public function getLang(): string
	{
		return $this->lang;
	}

	/**
	 * Set the lang path property.
	 *
	 * @param string $path
	 * @return $this
	 */
	public function setLangPath(string $path): Param
	{
		$this->langpath = $path;
		return $this;
	}

	/**
	 * Get the lang path property.
	 *
	 * @return string
	 */
	public function getLangPath(): string
	{
		return $this->langpath;
	}

	/**
	 * Set the via property.
	 *
	 * @param string $via
	 * @return $this
	 */
	public function setVia(string $via): Param
	{
		$this->via = $via;
		return $this;
	}

	/**
	 * Get the via property.
	 *
	 * @return string
	 */
	public function getVia(): string
	{
		return $this->via;
	}

	/**
	 * Set the id property.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setId(string $id): Param
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Get the id property.
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * Set the event payload property.
	 *
	 * @param string $payload
	 * @return $this
	 */
	public function setPayload(string $payload): Param
	{
		$this->payload = $payload;
		return $this;
	}

	/**
	 * Get the event payload property.
	 *
	 * @return string
	 */
	public function getPayload(): string
	{
		return $this->payload;
	}

	/**
	 * Set the custom campaign property.
	 *
	 * @param string $campaign
	 * @return $this
	 */
	public function setCampaign(string $campaign): Param
	{
		$this->campaign = $campaign;
		return $this;
	}

	/**
	 * Get the custom campaign property.
	 *
	 * @return string
	 */
	public function getCampaign(): string
	{
		return $this->campaign;
	}

	/**
	 * Set the deduplicate campaign property.
	 *
	 * @param bool $enable
	 * @return $this
	 */
	public function setDeduplicate(bool $enable): Param
	{
		$this->deduplicate = $enable;
		return $this;
	}

	/**
	 * Get the deduplicate campaign property.
	 *
	 * @return bool
	 */
	public function getDeduplicate(): bool
	{
		return $this->deduplicate;
	}

	/**
	 * Set the custom url tags property.
	 *
	 * @param string $tags
	 * @return $this
	 */
	public function setUrlTags(string $tags): Param
	{
		$this->urltags = $tags;
		return $this;
	}

	/**
	 * Get the custom url tags property.
	 *
	 * @return string
	 */
	public function getUrlTags(): string
	{
		return $this->urltags;
	}

	/**
	 * Set the from property.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function setFrom(string $email, string $name = ''): Param
	{
		$this->from = new Email($email, $name);
		return $this;
	}

	/**
	 * Get the from property.
	 *
	 * @return Email
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * Add reply email and reply name properties.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addReply(string $email, string $name = ''): Param
	{
		$e = new Email($email, $name);
		$this->reply[sha1($e->getEmail())] = $e;
		return $this;
	}

	/**
	 * Remove the targeted reply email property.
	 *
	 * @param string $email
	 * @return $this
	 */
	public function removeReply(string $email): Param
	{
		$handler = new Email($email);
		foreach ($this->reply as $k => $v) {
			if($handler->getEmail() === $v->getEmail()) {
				unset($this->reply[$k]);
			}
		}
		return $this;
	}

	/**
	 * Empty the reply property.
	 *
	 * @return $this
	 */
	public function clearReplies(): Param
	{
		$this->reply = [];
		return $this;
	}

	/**
	 * Get the replies property.
	 *
	 * @return Email[]
	 */
	public function getReplies(): array
	{
		return $this->reply;
	}

	/**
	 * Get the replies formated like "name <email> ; ...".
	 *
	 * @return string
	 */
	public function getReplyStr(): string
	{
		$str = '';
		foreach ($this->reply as $reply) {
			$str .= ($str ? ' ; ' : '') . $reply->str();
		}
		return $str;
	}

	/**
	 * Add recipient emal and recipient name (To) property.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addRecipient(string $email, string $name = ''): Param
	{
		$e = new Email($email, $name);
		$k = sha1($e->getEmail());
		if(in_array($k, $this->recipients)) {
			unset($this->copy[$k], $this->blind[$k]);
		}
		$this->to[$k] = $e;
		$this->recipients[$k] = $k;
		return $this;
	}

	/**
	 * Remove the targeted recipient (To) property.
	 *
	 * @param string $email
	 * @return $this
	 */
	public function removeRecipient(string $email): Param
	{
		$handler = new Email($email);
		foreach ($this->to as $k => $v) {
			if($handler->getEmail() === $v->getEmail()) {
				unset($this->to[$k]);
			}
		}
		return $this;
	}

	/**
	 * Empty recipients (To) property.
	 *
	 * @return $this
	 */
	public function clearRecipients(): Param
	{
		$this->to = [];
		return $this;
	}

	/**
	 * Get recipients (To) property.
	 *
	 * @return Email[]
	 */
	public function getRecipients(): array
	{
		return $this->to;
	}

	/**
	 * Get formated recipients like "name <email> ; ...".
	 *
	 * @return string
	 */
	public function getRecipientStr(): string
	{
		$str = '';
		foreach ($this->to as $to) {
			$str .= ($str ? ' ; ' : '') . $to->str();
		}
		return $str;
	}

	/**
	 * Add copy email and copy name (Cc) properties.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addCopy(string $email, string $name = ''): Param
	{
		$e = new Email($email, $name);
		$k = sha1($e->getEmail());
		if(!in_array($k, $this->recipients)) {
			$this->copy[$k] = $e;
			$this->recipients[$k] = $k;
		}
		return $this;
	}

	/**
	 * Remove the targeted copy (Cc) property.
	 *
	 * @param string $email
	 * @return $this
	 */
	public function removeCopy(string $email): Param
	{
		$handler = new Email($email);
		foreach ($this->copy as $k => $v) {
			if($handler->getEmail() === $v->getEmail()) {
				unset($this->copy[$k]);
			}
		}
		return $this;
	}

	/**
	 * Empty copies (Cc) property.
	 *
	 * @return $this
	 */
	public function clearCopies(): Param
	{
		$this->copy = [];
		return $this;
	}

	/**
	 * Get copies (Cc) property.
	 *
	 * @return Email[]
	 */
	public function getCopies(): array
	{
		return $this->copy;
	}

	/**
	 * Get the formated copy (Cc) like "name <email> ; ...".
	 *
	 * @return string
	 */
	public function getCopyStr(): string
	{
		$str = '';
		foreach ($this->copy as $copy) {
			$str .= ($str ? ' ; ' : '') . $copy->str();
		}
		return $str;
	}

	/**
	 * Add blind email and copy name (Bcc) properties.
	 *
	 * @param string $email
	 * @param string $name [optional]
	 * @return $this
	 */
	public function addBlind(string $email, string $name = ''): Param
	{
		$e = new Email($email, $name);
		$k = sha1($e->getEmail());
		if(!in_array($k, $this->recipients)) {
			$this->blind[$k] = $e;
			$this->recipients[$k] = $k;
		}
		return $this;
	}

	/**
	 * Remove the targeted blind (Bcc) property.
	 *
	 * @param string $email
	 * @return $this
	 */
	public function removeBlind(string $email): Param
	{
		$handler = new Email($email);
		foreach ($this->blind as $k => $v) {
			if($handler->getEmail() === $v->getEmail()) {
				unset($this->blind[$k]);
			}
		}
		return $this;
	}

	/**
	 * Empty blinds (Bcc) proprety.
	 *
	 * @return $this
	 */
	public function clearBlinds(): Param
	{
		$this->blind = [];
		return $this;
	}

	/**
	 * Get blinds (Bcc) property.
	 *
	 * @return Email[]
	 */
	public function getBlinds(): array
	{
		return $this->blind;
	}

	/**
	 * Get the formated blind (bcc) like "name <email> ; ...".
	 *
	 * @return string
	 */
	public function getBlindStr(): string
	{
		$str = '';
		foreach ($this->blind as $blind) {
			$str .= ($str ? ' ; ' : '') . $blind->str();
		}
		return $str;
	}

	/**
	 * Set the subject property.
	 *
	 * @param string $str
	 * @return $this
	 */
	public function setSubject(string $str): Param
	{
		$this->subject = (string) filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		return $this;
	}

	/**
	 * Get the subject property.
	 *
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject;
	}

	/**
	 * Set the HTML property.
	 *
	 * @param string $str
	 * @return $this
	 */
	public function setHtml(string $str): Param
	{
		$this->html = $str;
		return $this;
	}

	/**
	 * Get the subject property.
	 *
	 * @return string
	 */
	public function getHtml(): string
	{
		return $this->html;
	}

	/**
	 * Set the text property.
	 *
	 * @param string $str
	 * @return $this
	 */
	public function setText(string $str): Param
	{
		$this->text = strip_tags($str);
		return $this;
	}

	/**
	 * Get the subject property.
	 *
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * Set the template id property.
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setTemplateId(string $id): Param
	{
		$this->templateId = $id;
		return $this;
	}

	/**
	 * Get the template id property.
	 *
	 * @return string
	 */
	public function getTemplateId(): string
	{
		return $this->templateId;
	}

	/**
	 * Add variables property.
	 *
	 * @param array $variables
	 * @return $this
	 */
	public function addVariables(array $variables): Param
	{
		foreach ($variables as $k => $v) {
			$this->addVariable($k, $v);
		}
		return $this;
	}

	/**
	 * Add variable property.
	 *
	 * @param string $key
	 * @param string|string[] $value
	 * @return $this
	 */
	public function addVariable(string $key, $value): Param
	{
		$this->variables[$key] = $value;
		return $this;
	}

	/**
	 * Remove the targeted variable property.
	 *
	 * @param string $key
	 * @return $this
	 */
	public function removeVariable(string $key): Param
	{
		unset($this->variables[$key]);
		return $this;
	}

	/**
	 * Empty variables property.
	 *
	 * @return $this
	 */
	public function clearVariables(): Param
	{
		$this->variables = [];
		return $this;
	}

	/**
	 * Get variables property.
	 *
	 * @return array
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * Add attachment property.
	 *
	 * @param string $path
	 * @param string $name
	 * @return $this
	 */
	public function addAttachment(string $path, string $name): Param
	{
		$this->attachment[] = new Attachment($path, $name);
		return $this;
	}

	/**
	 * Remove the targeted attachement property.
	 *
	 * @param string $path
	 * @return $this
	 */
	public function removeAttachment(string $path): Param
	{
		$handler = new Attachment($path, 'handler');
		foreach ($this->attachment as $k => $v) {
			if($handler->getPath() === $v->getPath()) {
				unset($this->attachment[$k]);
			}
		}
		return $this;
	}

	/**
	 * Empty attachments property.
	 *
	 * @return $this
	 */
	public function clearAttachments(): Param
	{
		$this->attachment = [];
		return $this;
	}

	/**
	 * Get attachments property.
	 *
	 * @return Attachment[]
	 */
	public function getAttachments(): array
	{
		return $this->attachment;
	}

	/**
	 * Add inline attachment (embed) property.
	 *
	 * @param string $path
	 * @param string $name
	 * @param string $id [optional]
	 * @return $this
	 */
	public function addEmbed(string $path, string $name, string $id = null): Param
	{
		$embed = new Attachment($path, $name);
		if(null !== $id) {
			$embed->setId($id);
		}
		$this->embed[] = $embed;
		return $this;
	}

	/**
	 * Remove the targeted inline attachment (embed) property.
	 *
	 * @param string $path
	 * @return $this
	 */
	public function removeEmbed(string $path): Param
	{
		$handler = new Attachment($path, 'handler');
		foreach ($this->embed as $k => $v) {
			if($handler->getPath() === $v->getPath()) {
				unset($this->embed[$k]);
			}
		}
		return $this;
	}

	/**
	 * Empty inline attachments (embed) property.
	 *
	 * @return $this
	 */
	public function clearEmbeds(): Param
	{
		$this->embed = [];
		return $this;
	}

	/**
	 * Get inline attachments (embed) property.
	 *
	 * @return Attachment[]
	 */
	public function getEmbeds(): array
	{
		return $this->embed;
	}

	/**
	 * Get the targeted inline attachment (embed) property.
	 *
	 * @param string $id
	 * @return Attachment|null
	 */
	public function getEmbed(string $id)
	{
		foreach ($this->embed as $embed) {
			if($id === $embed->getId()) {
				return $embed;
			}
		}
		return null;
	}
}