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
	 * Pixelart constructor.
	 *
	 * @param int $squareSize
	 *
	 * @throws Exception
	 */
	public function __construct(int $squareSize) {
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
	 * @param string $imagePath
	 *
	 * @return string
	 * @throws Exception
	 */
	public function pixelize(string $imagePath): string {
		if (!$this->checkIfFileExists($imagePath)) {
			throw new Exception('File "' . $imagePath . '" could not be found.');
		}
		if (!$this->checkIfFileExtensionIsSupported($imagePath)) {
			throw new Exception('Given file extension is not supported');
		}



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
	 * @return bool
	 */
	protected function checkIfFileExtensionIsSupported(string $filepath): bool {
		return in_array(pathinfo($filepath, PATHINFO_EXTENSION), self::SUPPORTED_EXTENSIONS);
	}
}
