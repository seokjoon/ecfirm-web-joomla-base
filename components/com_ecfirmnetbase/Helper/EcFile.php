<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * @deprecated
 */
class EcFile
{

	/**
	 * @XXX
	 * @since 20170601
	 */
	const PATH_BASE = 'upload';

	public static function delete($paths)
	{
		if (!(is_array($paths))) $paths = array($paths);

		foreach ($paths as $i => &$path) {

			$path = self::rmDuplicatePath($path);
			//$path = JPATH_SITE . '/upload/' . $path; //EcDebug::lp($path);
			$path = JPATH_SITE . '/' . self::PATH_BASE . '/' . $path; //EcDebug::lp($path);

			$bool = File::exists($path);
			//if (! $bool) return false;
			if(!($bool)) {
				unset($paths[$i]);
				continue;
			}
		} //EcDebug::log($paths, __method__);

		return File::delete($paths);
	}

	public static function deleteDir($path)
	{
		$path = self::rmDuplicatePath($path);
		//return JFolder::delete(JPATH_SITE . '/upload/' . $path);
		return Folder::delete(JPATH_SITE . '/' . self::PATH_BASE . '/' . $path);
	}

	/**
	 * @example
	 */
	public static function getFileModified($path)
	{ //EcDebug::log('getFileModified: '.$path);

		$path = self::rmDuplicatePath($path);
		//$path = JPATH_SITE . '/upload/' . $path
		$path = JPATH_SITE . '/' . self::PATH_BASE . '/' . $path;

		$fileModified = filemtime($path); //$stat = stat($path);

		if ($fileModified === false) return 0;
		else return $fileModified;
	}

	/**
	 * @example
	 */
	public static function getFiles($path)
	{ //EcDebug::log('getFiles: '.$path);

		$path = self::rmDuplicatePath($path);
		//$pathUpload = JPATH_SITE . '/upload';
		$pathUpload = JPATH_SITE . '/' . self::PATH_BASE;

		$pathFile = $pathUpload . '/' . $path;
		$exclude = array( 'index.html' );

		$files = Folder::files($pathFile, null, null, null, $exclude, null, true);

		$reg = new Registry(); //FIXME: add loader
		$reg->loadArray($files);
		$files = $reg->toObject();

		return $files;
	}

	/**
	 * @example
	 */
	public static function getFilesModified($path)
	{ //EcDebug::log('getFilesModified: '.$path);

		$path = self::rmDuplicatePath($path);
		//$path = JPATH_SITE . '/upload/' . $path;
		$path = JPATH_SITE . '/' . self::PATH_BASE . '/' . $path;

		$exclude = array( 'index.html' );

		$files = array();
		$modifieds = array();

		if (is_dir($path))
			$files = Folder::files($path, null, null, null, $exclude, null, true);

		foreach ($files as $file) $modifieds[$file] = filemtime($path . $file);

		return $modifieds;
	}

	/**
	 * @XXX
	 * @since 20170601
	 */
	private static function rmDuplicatePath($path)
	{
		return str_replace(self::PATH_BASE . '/', '', $path);
	}

	public static function setFile($path)
	{ //EcDebug::log($_FILES['file']); //name, tmp_name
		$src = $_FILES['file']['tmp_name'];
		$dest = $_FILES['file']['name'];

		$path = self::rmDuplicatePath($path);
		//$path = JPATH_SITE . '/upload/' . $path . '/'; //$path = JFactory::getConfig()->get('tmp_path').'/';
		$path = JPATH_SITE . '/' . self::PATH_BASE . '/' . $path . '/'; //$path = JFactory::getConfig()->get('tmp_path').'/';

		return File::upload($src, $path . $dest);
	}

	public static function setFileByName($params, $nameKey)
	{
		//$pathRelative = 'upload/' . $nameKey . '/';
		$pathRelative = self::PATH_BASE . '/' . $nameKey . '/';
		$path = JPATH_SITE . '/' . $pathRelative;
		$nameFile = time() . '-' . rand() . '.' . $params['name'];

		File::upload($params['tmp_name'], $path . $nameFile);

		$paths['file'] = $pathRelative . $nameFile;

		return $paths;
	}

	public static function setFileByUser($params, $nameKey)
	{
		//$pathRelative = 'upload/user.' . JFactory::getUser()->id . '/' . $nameKey . '/';
		$pathRelative = self::PATH_BASE . '/user.' . Factory::getUser()->id . '/' . $nameKey . '/';
		$path = JPATH_SITE . '/' . $pathRelative;
		$nameFile = time() . '-' . rand() . '.' . $params['name'];

		File::upload($params['tmp_name'], $path . $nameFile);

		$paths['file'] = $pathRelative . $nameFile;

		return $paths;
	}
}