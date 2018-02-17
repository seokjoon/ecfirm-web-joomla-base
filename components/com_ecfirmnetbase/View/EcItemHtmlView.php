<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\View;

use function implode;
use Joomla\CMS\Factory;
use JViewGenericdataexception;

defined('_JEXEC') or die;

abstract class EcItemHtmlView extends EcHtmlView
{

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->nameKey = $this->getName();
		$this->plural = false;
	}

	public function display($tpl = null)
	{
		$this->item = $this->getItem(); //$this->item = $this->get('Item');

		if (count($errors = $this->get('Errors')))
			throw new JViewGenericdataexception(implode("\n", $errors), 500);

		parent::display($tpl);
	}

	protected function getItem($valueKey = 0)
	{
		$valueKey = ($valueKey == 0) ? Factory::getApplication()->input->getInt($this->nameKey) : $valueKey;

		$model = $this->getModel($this->getName());
		$model->setState($this->nameKey, $valueKey);

		$item = $this->get('Item', $this->nameKey);
		if(empty($item)) return $item;

		$state = $this->get('State', $this->nameKey);
		if ((isset($state->enabledPlugin)) && ($state->enabledPlugin))
			$item = $this->eventPlugin($item);

		return $item;
	}
}