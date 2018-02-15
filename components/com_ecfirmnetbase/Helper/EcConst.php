<?php
/**
 * @package joomla.ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

defined('_JEXEC') or die('Restricted access');

class EcConst
{

	const UNIXTIME_DAY = 86400;

	const UNIXTIME_WEEK = 604800;

	const USER_GROUP_ADMINISTRATOR = 7;

	const USER_GROUP_AUTHOR = 3;

	const USER_GROUP_EDITOR = 4;

	const USER_GROUP_GUEST = 9;

	const USER_GROUP_NOT_DEFINED = - 1;

	const USER_GROUP_PUBLIC = 1;

	const USER_GROUP_REGISTERED = 2;

	const USER_GROUP_SUPERUSER = 8;

	private static $prefix = 'ecfirmnet';

	public static function getPrefix()
	{
		return self::$prefix;
	}

	public static function setPrefix($namePrefix)
	{
		self::$prefix = $namePrefix;
	}
}