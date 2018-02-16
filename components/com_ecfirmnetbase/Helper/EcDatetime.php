<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

class EcDatetime
{

	public static function getTime()
	{
		list ($usec, $sec) = explode(" ", microtime());

		return ((float) $usec + (float) $sec);
	}

	public static function interval($date)
	{
		$interval = strtotime('now') - strtotime($date);
		$minute = ceil($interval / 60);
		$hour = floor($minute / 60);
		$day = floor($hour / 24);
		$month = floor($day / 30);

		//if($month >= 1) $interval = $month.JText::_('COM_EC_INTERVAL_MONTH');
		if ($month >= 1)
			$interval = HTMLHelper::_('date', $date, Text::_('COM_EC_DATE_FORMAT_1'));
		else
			if ($day >= 1) $interval = $day . Text::_('COM_EC_DATE_DAY');
			else
				if ($hour >= 1) $interval = $hour . Text::_('COM_EC_DATE_HOUR');
				else $interval = $minute . Text::_('COM_EC_DATE_MINUTE');

		return $interval;
	}
}