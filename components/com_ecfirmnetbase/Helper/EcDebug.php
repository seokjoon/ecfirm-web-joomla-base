<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Joomla\CMS\Log\Log;

defined('_JEXEC') or die();

class EcDebug
{

	public static function log($items, $key = '')
	{
		jimport('joomla.log.log');

		Log::addLogger(array(
			'text_file' => 'ec.php'
		));

		if (is_array($items))
			$type = 'array';
		elseif (is_object($items))
			$type = 'object';

		if (isset($type)) {

			Log::add($type, Log::DEBUG, 'type');

			foreach ($items as $key => $item) {
				if ((is_array($item)) || is_object($item))
					$item = 'array or object';

				Log::add($item, Log::DEBUG, $key);
			}
		} else
			Log::add($items, Log::DEBUG, $key);
	}

	public static function lp($value = null, $exit = false, $call = null)
	{
		if (! empty($call))
			echo '<br />' . $call; //__method__;
		if (empty($value))
			$value = '<br />lp: empty<br />'; //$GLOBALS;

		echo '<pre>' . print_r($value, 1) . '</pre>';

		if ($exit)
			jexit();
	}
}