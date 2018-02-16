<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class EcPermit
{

	public static function allowAdd($userId = 0, $group = EcConst::USER_GROUP_REGISTERED)
	{
		//if ($user == 0) $user = Factory::getUser()->id;
		$user = ($userId == 0) ? Factory::getUser()->id : Factory::getUser($userId)->id;

		$bool = EcUser::isGroup($group, $user);

		return $bool;
	}

	public static function allowEdit($item = null, $userId = 0, $group = EcConst::USER_GROUP_ADMINISTRATOR)
	{
		$bool = ($item->user == $userId);
		if(!($bool)) {
			$user = ($userId == 0) ? Factory::getUser()->id : Factory::getUser($userId)->id;
			$bool = EcUser::isGroup($group, $user);
		}

		return $bool;
	}

	/**
	 * @deprecated: DELETE ME
	 */
	public static function allowEditLegacy($item = null, $user = 0)
	{ //EcDebug::lp($item, true);
		if ($user == 0) $user = Factory::getUser()->id;

		if ($item->user == $user) return true;
		else return false; //parent::allowEdit($data, $nameKey);
	}
}