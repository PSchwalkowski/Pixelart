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
	protected $cropPartsAmount = [
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
	 * @return void
	 * @throws Exception
	 */
	public function pixelize(string $filepath, string $savePathAndName) {
		if (!$this->checkIfFileExists($filepath)) {
			throw new Exception('File "' . $filepath . '" could not be found.');
		}
		if (!$this->checkIfFileExtensionIsSupported($filepath)) {
			throw new Exception('Given file extension is not supported');
		}

		$this->setImageResourceFromPath($filepath)->setImageResourceSize($filepath)->setCropPartsAmount();

		$colors = $this->cropImageAndGetColors();
		$image = $this->createImageFromColorsInformations($colors);

		imagejpeg($image, $savePathAndName);
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
	 * @return Pixelart
	 */
	protected function setCropPartsAmount(): self {
		$this->cropPartsAmount = [
			'horizontal' => (int) $this->imageSize['width'] / $this->squareSize,
			'vertical' => (int) $this->imageSize['height'] / $this->squareSize,
		];

		return $this;
	}

	/**
	 * @return array
	 */
	protected function cropImageAndGetColors(): array {
		$colors = [];
		for ($i = 0; $i < $this->cropPartsAmount['vertical']; $i++) {
			for ($j = 0; $j < $this->cropPartsAmount['horizontal']; $j++) {
				$croppedImage = imagecrop($this->image, [
					'x' => $j * $this->squareSize,
					'y' => $i * $this->squareSize,
					'width' => $this->squareSize,
					'height' => $this->squareSize,
				]);

				$colors[$i][$j] = $this->getMainColorFromImage($croppedImage);
			}
		}

		return $colors;
	}

	/**
	 * @param $image
	 *
	 * @return array
	 */
	protected function getMainColorFromImage($image): array {
		$colors = [];
		for ($i = 0; $i < $this->squareSize; $i++) {
			for ($j = 0; $j < $this->squareSize; $j++) {
				$colorIndex = imagecolorat($image, $i, $j);

				if (!isset($colors[$colorIndex])) {
					$colors[$colorIndex] = 1;
				} else {
					$colors[$colorIndex] += 1;
				}
			}
		}

		$colorIndex = array_search(
			max($colors),
			$colors
		);

		return [
			'red' => ($colorIndex >> 16) & 0xFF,
			'green' => ($colorIndex >> 8) & 0xFF,
			'blue' => $colorIndex & 0xFF,
		];
	}

	/**
	 * @param array $colors
	 *
	 * @return false|resource
	 */
	protected function createImageFromColorsInformations(array $colors) {
		$image = imagecreatetruecolor($this->imageSize['width'], $this->imageSize['height']);

		foreach ($colors as $row => $colorsRow) {
			foreach ($colorsRow as $col => $color) {
				$imagePart = imagecreatetruecolor($this->squareSize, $this->squareSize);

				imagefilledrectangle(
					$imagePart,
					0,
					0,
					$this->squareSize,
					$this->squareSize,
					imagecolorallocate(
						$imagePart,
						$color['red'],
						$color['green'],
						$color['blue']
					)
				);

				imagecopymerge(
					$image,
					$imagePart,
					$col * $this->squareSize,
					$row * $this->squareSize,
					0,
					0,
					$this->squareSize,
					$this->squareSize,
					100
				);

				imagedestroy($imagePart);
			}
		}

		return $image;
	}
}
