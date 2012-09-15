<?php
/**
 * Transfer Validation File
 *
 * Copyright (c) 2007-2011 David Persson
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 * CakePHP version 1.3
 *
 * @package    media
 * @subpackage media.libs
 * @copyright  2007-2011 David Persson <davidpersson@gmx.de>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link       http://github.com/davidpersson/media
 */

App::uses('Validation', 'Utility');
App::uses('Media/MediaValidation', 'Lib');

/**
 * Transfer Validation Class
 *
 * @package    media
 * @subpackage media.libs
 */
class TransferValidation extends MediaValidation {

/**
 * Checks if subject is transferable
 *
 * @param mixed $check Path to file in local FS, URL or file-upload array
 * @return boolean
 */
	public static function resource($check) {
		if (TransferValidation::fileUpload($check)
		 || TransferValidation::uploadedFile($check) /* This must appear above file */
		 || MediaValidation::file($check)
		 || TransferValidation::url($check)) {
		  	return !TransferValidation::blank($check);
		}
		return false;
	}

/**
 * Checks if resource is not blank or empty
 *
 * @param mixed $check Array or string
 * @return boolean
 */
	public static function blank($check) {
		if (empty($check)) {
			return true;
		}
		if (TransferValidation::fileUpload($check) && $check['error'] == UPLOAD_ERR_NO_FILE) {
			return true;
		}
		if (is_string($check) && Validation::blank($check)) {
			return true;
		}
		return false;
	}

/**
 * Identifies a file upload array
 *
 * @param mixed $check
 * @return boolean
 */
	public static function fileUpload($check) {
		if (!is_array($check)) {
			return false;
		}
		if (!array_key_exists('name',$check)
		 || !array_key_exists('type',$check)
		 || !array_key_exists('tmp_name',$check)
		 || !array_key_exists('error',$check)
		 || !array_key_exists('size',$check)) {
			return false;
		}
		return true;
	}

/**
 * Checks if subject is an uploaded file
 *
 * @param mixed $check
 */
	public static function uploadedFile($check) {
		return MediaValidation::file($check) && is_uploaded_file($check);
	}

/**
 * Validates url
 *
 * @param string string to check
 * @param array options for allowing different url parts currently only scheme is supported
 */
	public static function url($check, $options = array()) {
		if (!is_string($check)) {
			return false;
		}
		if (isset($options['scheme'])) {
			if (!preg_match('/^(' . implode('|', (array) $options['scheme']) . ':)+/', $check)) {
				return false;
			}
		}
		return Validation::url($check);
	}
}
