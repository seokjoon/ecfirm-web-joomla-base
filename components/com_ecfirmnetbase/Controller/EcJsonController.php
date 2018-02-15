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

	public function add()
	{
		echo new JsonResponse(null, null, !(parent::add()));
	}

	public function cancel($nameKey = null)
	{
		echo new JsonResponse(null, null, !(parent::cancel()));
	}




}