<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

use Joomla\CMS\Response\JsonResponse;
use Joomla\Component\EcfirmNetBase\Site\Controller\EcController;

defined('_JEXEC') or die;

abstract class EcJsonController extends EcController
{

	public function delete()
	{
		$bool = parent::delete();
		echo new JsonResponse(null, null, !($bool));

		return $bool;
	}

	public function getItem($valueKey = 0, $nameKey = '')
	{
		$item = parent::getItem($valueKey, $nameKey);

		if (is_object($item)) echo new JsonResponse($item);
		else if (is_bool($item)) echo new JsonResponse(null, null, ! $item);
	}

	public function getItems($name = null)
	{
		$items = parent::getItems($name);

		if (is_array($items)) echo new JsonResponse($items);
		else if (is_bool($items)) echo new JsonResponse(null, null, ! $items);
	}

	public function save($nameKey = null, $urlVar = null)
	{
		echo new JsonResponse(null, null, ! (parent::save($nameKey, $urlVar)));
	}

	protected function setRedirectParams($params = array())
	{
		$params['format'] = 'json';

		parent::setRedirectParams($params);
	}
}