<?php
namespace Burzum\FileStorage\Storage;

use Cake\Core\Configure;
use Cake\Filesystem\File;

/**
 * Utility methods for which I could not find a better place
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class StorageUtils {

/**
 * Return file extension from a given filename
 *
 * @param string $name
 * @param boolean $realFile
 * @link http://php.net/manual/en/function.pathinfo.php
 * @return false|string string or false
 */
	public static function fileExtension($name, $realFile = false) {
		if ($realFile) {
			$result = pathinfo($name, PATHINFO_EXTENSION);
			if (empty($result)) {
				return false;
			}
		}
		return substr(strrchr($name, '.'), 1);
	}

/**
 * Builds a semi-random path based on a given string to avoid having thousands of files
 * or directories in one directory. This would result in a slowdown on most file systems.
 *
 * Works up to 5 level deep
 *
 * @deprecated Use the randomPath() method from the BasePathBuilder instead.
 * @link https://www.box.com/blog/crc32-checksums-the-good-the-bad-and-the-ugly/
 * @throws InvalidArgumentException
 * @param mixed $string
 * @param integer $level 1 to 5
 * @return null|string
 */
	public static function randomPath($string, $level = 3) {
		if (!$string) {
			throw new \InvalidArgumentException('First argument is not a string!');
		}
		$string = crc32($string);
		$decrement = 0;
		$path = null;
		for ($i = 0; $i < $level; $i++) {
			$decrement = $decrement - 2;
			$path .= sprintf("%02d" . DS, substr(str_pad('', 2 * $level, '0') . $string, $decrement, 2));
		}
		return $path;
	}

/**
 * Helper method to trim last trailing slash in file path
 *
 * @param string $path Path to trim
 * @return string Trimmed path
 */
	public static function trimPath($path) {
		$len = strlen($path);
		if ($path[$len - 1] == '\\' || $path[$len - 1] == '/') {
			$path = substr($path, 0, $len - 1);
		}
		return $path;
	}

/**
 * Converts windows to linux pathes and vice versa
 *
 * @param string
 * @return string
 */
	public static function normalizePath($string) {
		if (DS == '\\') {
			return str_replace('/', '\\', $string);
		} else {
			return str_replace('\\', '/', $string);
		}
	}

/**
 * Method to normalize the annoying inconsistency of the $_FILE array structure
 *
 * @link http://www.php.net/manual/en/features.file-upload.multiple.php#109437
 * @param array $array
 * @return array Empty array if $_FILE is empty, if not normalize array of Filedata.{n}
 */
	public static function normalizeGlobalFilesArray($array = null) {
		if (empty($array)) {
			$array = $_FILES;
		}
		$newfiles = array();
		if (!empty($array)) {
			foreach ($array as $fieldname => $fieldvalue) {
				foreach ($fieldvalue as $paramname => $paramvalue) {
					foreach ((array)$paramvalue as $index => $value) {
						$newfiles[$fieldname][$index][$paramname] = $value;
					}
				}
			}
		}
		return $newfiles;
	}

/**
 * Serializes and then hashes an array of operations that are applied to an image
 *
 * @param array $operations
 * @return string
 */
	public static function hashOperations($operations) {
		self::ksortRecursive($operations);
		return substr(md5(serialize($operations)), 0, 8);
	}

/**
 * Generates the hashes for the different image version configurations.
 *
 * @param string|array $configPath
 * @return array
 */
	public static function generateHashes($configPath = 'FileStorage') {
		if (is_array($configPath)) {
			$imageSizes = $configPath;
		} else {
			$imageSizes = Configure::read($configPath . '.imageSizes');
		}
		if (is_null($imageSizes)) {
			throw new \RuntimeException(sprintf('Image processing configuration in "%s" is missing!', $configPath . '.imageSizes'));
		}
		self::ksortRecursive($imageSizes);
		foreach ($imageSizes as $model => $version) {
			foreach ($version as $name => $operations) {
				Configure::write($configPath . '.imageHashes.' . $model . '.' . $name, self::hashOperations($operations));
			}
		}
		return Configure::read($configPath . '.imageHashes');
	}

/**
 * Recursive ksort() implementation
 *
 * @param array $array
 * @param integer
 * @return boolean
 * @link https://gist.github.com/601849
 */
	public static function ksortRecursive(&$array, $sortFlags = SORT_REGULAR) {
		if (!is_array($array)) {
			return false;
		}
		ksort($array, $sortFlags);
		foreach ($array as &$arr) {
			self::ksortRecursive($arr, $sortFlags);
		}
		return true;
	}

/**
 * Returns an array that matches the structure of a regular upload for a local file
 *
 * @param $file
 * @param string File with path
 * @return array Array that matches the structure of a regular upload
 */
	public static function uploadArray($file, $filename = null) {
		$File = new File($file);
		if (empty($fileName)) {
			$filename = basename($file);
		}
		return [
			'name' => $filename,
			'tmp_name' => $file,
			'error' => 0,
			'type' => $File->mime(),
			'size' => $File->size()
		];
	}

/**
 * Gets the hash of a file.
 *
 * You can use this to compare if you got two times the same file uploaded.
 *
 * @param string $file Path to the file on your local machine.
 * @param string $method 'md5' or 'sha1'
 * @throws \InvalidArgumentException
 * @link http://php.net/manual/en/function.md5-file.php
 * @link http://php.net/manual/en/function.sha1-file.php
 * @link http://php.net/manual/en/function.sha1-file.php#104748
 * @return string
 */
	public static function getFileHash($file, $method = 'sha1') {
		if ($method === 'md5') {
			return md5_file($file);
		}
		if ($method === 'sha1') {
			return sha1_file($file);
		}
		throw new \InvalidArgumentException(sprintf('Invalid hash method "%s" provided!', $method));
	}
}
