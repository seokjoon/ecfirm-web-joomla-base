<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class EcUrl
{

	public static function getItemId()
	{
		$itemId = Factory::getApplication()->getMenu()->getActive()->id;
		return (is_numeric($itemId)) ? $itemId : 0;
	}

	public static function getMenuActiveQuery($query)
	{
		return Factory::getApplication()->getMenu()->getActive()->query[$query];
	}
}