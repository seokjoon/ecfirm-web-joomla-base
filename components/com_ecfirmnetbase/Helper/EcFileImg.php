<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Image\Image;
use Joomla\Filesystem\File;

defined('_JEXEC') or die();

/**
 * @deprecated
 */
class EcFileImg extends EcFile
{

	/**
	 * @throws
	 * @see http://php.net/manual/en/function.image-type-to-mime-type.php
	 */
	public static function getType($type)
	{
		$ext = str_replace('image/', '', $type);
		return ($ext == 'jpeg') ? 'jpg' : $ext;
	}

	public static function setFileImgByName($params, $nameKey, $nameCol = 'img')
	{ //EcDebug::log($params, __method__);
		//$pathRelative = 'upload/shop.'.$jform['shop'].'/'.$nameKey.'/'; //EcDebug::log($path);
		$pathRelative = 'upload/' . $nameKey . '/' . $nameCol . '/'; //EcDebug::log($path);
		$path = JPATH_SITE . '/' . $pathRelative;
		////JFile::upload($params['tmp_name'], $path.$params['name']);
		//$nameFile = $jform[$nameKey].'.'.(self::getType($params['type']));
		$nameFile = time() . '-' . rand() . '.' . (self::getType($params['type']));

		File::upload($params['tmp_name'], $path . $nameFile);

		$jImg = new Image($path . $nameFile);

		$ratioUnit = 256;
		if((isset($params['ratio'])) && ($params['ratio'] !== false)) {
			if($params['ratio'] === true) {
				$ratioHeight = $ratioUnit * ($jImg->getHeight() / $jImg->getWidth()); //EcDebug::log($ratioHeight);
				if($ratioHeight > ($ratioUnit * 2)) $ratioHeight = $ratioUnit * 2;
				$params['ratio'] = $ratioUnit . 'x' . $ratioHeight;
			} else if(strpos($params['ratio'], 'x') !== false) {
				$ratioWidth = explode('x', $params['ratio'])[0];
				$ratioHeight = $ratioWidth * ($jImg->getHeight() / $jImg->getWidth()); //EcDebug::log($ratioHeight);
				if($ratioHeight > ($ratioWidth * 2)) $ratioHeight = $ratioWidth * 2 ;
				$params['ratio'] = $ratioWidth . 'x' . $ratioHeight;
			} //else custom WxH ratio
		} else $params['ratio'] = $ratioUnit . 'x' . $ratioUnit; //EcDebug::log($params['ratio'], __method__); jexit();

		//3: 비율에 크기를 맞춤 //1: 크기에 비율을 맞춤 //5: 축소&crop하여 크기와 비율을 맞춤
		$thumbs = $jImg->createThumbs($params['ratio'], 5);

		$src = basename($thumbs[0]->getPath()); //EcDebug::log($src, __method__);
		$pathThumbs = $path . '/thumbs/'; //EcDebug::log($src.':'.$nameFile, __method__);

		if (File::move($src, $nameFile, $pathThumbs)) {
			$paths[$nameCol] = $pathRelative . $nameFile;
			$paths[$nameCol . 'thumb'] = $pathRelative . 'thumbs/' . $nameFile;
			return $paths;
		} else return false;
	}

	//public static function setFileImgShop($jform, $params, $nameKey) { //EcDebug::log($params, __method__);
	public static function setFileImgByUser($params, $nameKey, $nameCol = 'img')
	{ //EcDebug::log($params, __method__);
		//$pathRelative = 'upload/shop.'.$jform['shop'].'/'.$nameKey.'/'; //EcDebug::log($path);
		$pathRelative = 'upload/user.' . Factory::getUser()->id . '/' . $nameKey . '/' . $nameCol . '/'; //EcDebug::log($path);
		$path = JPATH_SITE . '/' . $pathRelative;
		////JFile::upload($params['tmp_name'], $path.$params['name']);
		//$nameFile = $jform[$nameKey].'.'.(self::getType($params['type']));
		$nameFile = time() . '-' . rand() . '.' . (self::getType($params['type']));

		File::upload($params['tmp_name'], $path . $nameFile);

		$jImg = new Image($path . $nameFile);

		$params['rate'] = ((! (isset($params['rate']))) || (empty($params['rate']))) ? '256x256' : $params['rate'];
		//3: 비율에 크기를 맞춤 //1: 크기에 비율을 맞춤 //5: 축소&crop하여 크기와 비율을 맞춤

		$thumbs = $jImg->createThumbs($params['rate'], 5);
		$src = basename($thumbs[0]->getPath()); //EcDebug::log($src, __method__);
		$pathThumbs = $path . '/thumbs/'; //EcDebug::log($src.':'.$nameFile, __method__);

		if (File::move($src, $nameFile, $pathThumbs)) {
			$paths[$nameCol] = $pathRelative . $nameFile;
			$paths[$nameCol . 'thumb'] = $pathRelative . 'thumbs/' . $nameFile;
			return $paths;
		} else return false;
	}

	/**
	 * @deprecated
	 * @example DO NOT DELETE ME
	 */
	public static function setFileImgLegacy($path = null, $thumbs = false)
	{
		if (!(parent::setFile($path))) return false;
		$fileName = $_FILES['file']['name'];
		if (!($thumbs)) return true;
		//EcDebug::log(JPATH_SITE.'/upload/'.$path.'/'.$fileName);

		$jImg = new Image(JPATH_SITE . '/upload/' . $path . '/' . $fileName);
		//EcDebug::log($jImg->getHeight());

		$thumbs = $jImg->createThumbs('470x470', 3);
		$src = basename($thumbs[0]->getPath()); //EcDebug::log($src);

		////$img = $jImg->resize('40', '40', true);
		////$img->toFile(JPATH_SITE.'/upload/'.$path.'/thumbs/');
		//$src = JFile::stripExt($fileName).'_470x470.'.JFile::getExt($fileName);
		$path = JPATH_SITE . '/upload/' . $path . '/thumbs/';
		//EcDebug::log($fileName.':'.$src);

		return File::move($src, $fileName, $path);
	}
}