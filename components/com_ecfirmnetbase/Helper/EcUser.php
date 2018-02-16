<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class EcUser
{

	public static function isGroup($in, $user = null)
	{
		if (empty($user)) $user = Factory::getUser();

		foreach ($user->groups as $group) if ($group == $in) return true;

		return false;
	}
}