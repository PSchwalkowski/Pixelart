<?php

namespace PS;

use Exception;

class Pixelart {

	/**
	 * List of supported files extensions
	 */
	const SUPPORTED_EXTENSIONS = ['jpg'];

	/**
	 * @var int
	 */
	protected $squareSize;

	/**
	 * @var null
	 */
	protected $image = null;

	/**
	 * @var array
	 */
	protected $imageSize = [
		'width' => 0,
		'height' => 0,
	];

	/**
	 * @var array
	 */
	protected $cropPartsSizes = [
		'horizontal' => 0,
		'vertical' => 0,
	];


	/**
	 * Pixelart constructor.
	 *
	 * @param int $squareSize
	 *
	 * @throws Exception
	 */
	public function __construct(int $squareSize = 10) {
		$this->setSquareSize($squareSize);
	}

	/**
	 * @param int $squareSize
	 *
	 * @return Pixelart
	 * @throws Exception
	 */
	public function setSquareSize(int $squareSize): self {
		if ($squareSize <= 2) {
			throw new Exception('Minimal square size is 2. "' . $squareSize . '" given.');
		}

		$this->squareSize = $squareSize;

		return $this;
	}

	/**
	 * @param string $filepath
	 *
	 * @return string
	 * @throws Exception
	 */
	public function pixelize(string $filepath): string {
		if (!$this->checkIfFileExists($filepath)) {
			throw new Exception('File "' . $filepath . '" could not be found.');
		}
		if (!$this->checkIfFileExtensionIsSupported($filepath)) {
			throw new Exception('Given file extension is not supported');
		}

		$this->setImageResourceFromPath($filepath)->setImageResourceSize($filepath)->setCropPartSizes();

		echo '<pre>';
		print_r($this);
		echo '</pre>';
		
		return '';
	}

	/**
	 * @param string $filepath
	 *
	 * @return bool
	 */
	protected function checkIfFileExists(string $filepath): bool {
		return file_exists($filepath);
	}

	/**
	 * @param string $filepath
	 *
	 * @return mixed
	 */
	protected function getFileExtension(string $filepath) {
		return pathinfo($filepath, PATHINFO_EXTENSION);
	}

	/**
	 * @param string $filepath
	 *
	 * @return bool
	 */
	protected function checkIfFileExtensionIsSupported(string $filepath): bool {
		return in_array($this->getFileExtension($filepath), self::SUPPORTED_EXTENSIONS);
	}

	/**
	 * @param string $filepath
	 *
	 * @return Pixelart
	 */
	protected function setImageResourceFromPath(string $filepath): self {
		switch ($this->getFileExtension($filepath)) {
			case 'jpg':
			case 'jpeg':
				$this->image = imagecreatefromjpeg($filepath);
				break;
		}

		return $this;
	}

	/**
	 * @param string $filepath
	 *
	 * @return Pixelart
	 */
	protected function setImageResourceSize(string $filepath): self {
		$size = getimagesize($filepath);

		$this->imageSize = [
			'width' => $size[0],
			'height' => $size[1],
		];

		return $this;
	}

	/**
	 * @return $this
	 */
	private function setCropPartSizes() {
		$this->cropPartsSizes = [
			'horizontal' => $this->imageSize['width'] / $this->squareSize,
			'vertical' => $this->imageSize['height'] / $this->squareSize,
		];

		return $this;
	}
}
