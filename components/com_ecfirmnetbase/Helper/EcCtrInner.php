<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Exception;
use Joomla\Component\EcfirmNetBase\Site\Controller\EcController;

defined('_JEXEC') or die();

abstract class EcCtrInner
{

	protected static $instance = null;

	protected static $nameKey;

	protected static $nameCom;

	protected function __construct($nameCom, $nameKey)
	{
		self::$nameCom = $nameCom;
		self::$nameKey = $nameKey;
	}

	protected function getControllerLegacy($nameCom)
	{
		$nameCom = (empty($nameCom)) ? self::$nameCom : $nameCom;

		try {
			return EcController::getInstance($nameCom);
		} catch (Exception $e) {
			EcDebug::log($e->getMessage());
		}
	}

	public static function getInstance($nameCom, $nameKey)
	{
		if (($nameCom == self::$nameCom) && ($nameKey == self::$nameKey) && (is_object(self::$instance)))
			return self::$instance;

		$class = ucfirst($nameCom) . 'CtrInner' . ucfirst($nameKey);

		if (class_exists($class)) self::$instance = new $class($nameCom, $nameKey);

		return self::$instance;
	}

	public function getModel($nameKey = null)
	{
		if (empty($nameKey)) $nameKey = self::$nameKey;

		try {
			return $this->getControllerLegacy(self::$nameCom)->getModel($nameKey);
		} catch (Exception $e) {
			EcDebug::log($e->getMessage());
		}
	}

	public function test()
	{
		EcDebug::lp(self::$nameCom . '.' . self::$nameKey);
		EcDebug::lp(__method__);
		EcDebug::log(self::$nameCom . '.' . self::$nameKey, __method__);
	}
}