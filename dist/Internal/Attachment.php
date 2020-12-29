<?php
namespace Coercive\Utility\Email\Internal;

use Mimey\MimeTypes;

/**
 * Attachment handler
 *
 * This file is part of the Coercive/Email package.
 * @copyright Anthony Moral <contact@coercive.fr>
 * @package Coercive\Utility\Email
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed
 * with this source code.
 */
class Attachment
{
	/** @var string id */
	private $id = null;

	/** @var string Name */
	private $name = '';

	/** @var string Filepath */
	private $path = '';

	/**
	 * Attachment constructor.
	 *
	 * @param string $path
	 * @param string $name
	 * @param string $id [optional]
	 * @return void
	 */
	public function __construct(string $path, string $name, string $id = null)
	{
		$this->setPath($path);
		$this->setName($name);
		if(null !== $id) {
			$this->setId($id);
		}
	}

	/**
	 * SETTER id
	 *
	 * @param string $id
	 * @return $this
	 */
	public function setId(string $id): Attachment
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * GETTER id
	 *
	 * @return string
	 */
	public function getId(): string
	{
		return null === $this->id ? sha1($this->name.$this->path) : $this->id;
	}

	/**
	 * SETTER path
	 *
	 * @param string $path
	 * @return $this
	 */
	public function setPath(string $path): Attachment
	{
		$this->path = (string) $path;
		return $this;
	}

	/**
	 * GETTER path
	 *
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * SETTER name
	 *
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name): Attachment
	{
		$this->name = (string) filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		return $this;
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
	 * GET formatted data
	 *
	 * @param bool $inline [optional]
	 * @return array
	 */
	public function get(bool $inline = false): array
	{
		if(!$this->name || !($content = $this->getBase64Content())) {
			return [];
		}
		$data = [
			'ContentType' => $this->getMimeType(),
			'Filename' => $this->name,
			'Base64Content' => $content,
		];
		if($inline) {
			$data['ContentID'] = $this->getId();
		}
		return $data;
	}

	/**
	 * GETTER content B64 formatted
	 *
	 * @return string
	 */
	public function getBase64Content(): string
	{
		if(!is_file($this->path)) {
			return '';
		}
		if(!($content = @file_get_contents($this->path))) {
			return '';
		}
		return base64_encode($content);
	}

	/**
	 * Get mime type of the target file or for the given filename
	 *
	 * @return string
	 */
	public function getMimeType(): string
	{
		# Detect extension
		$ext = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
		if(!$ext) {
			return 'unknown/unknown';
		}

		# Detect Mime
		$mimey = new MimeTypes;
		$mime = (string) $mimey->getMimeType($ext);
		return $mime ?: "unknown/$ext";
	}
}