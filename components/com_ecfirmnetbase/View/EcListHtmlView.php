<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\View;

use JViewGenericdataexception;

defined('_JEXEC') or die;

abstract class EcListHtmlView extends EcHtmlView
{
	protected $items;

	protected $pagination;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->nameKey = substr($this->getName(), 0, - 1);
		$this->plural = true;
	}

	public function display($tpl = null)
	{
		$this->items = $this->getItems(); //$this->items = $this->get('Items');

		$this->pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
			throw new JViewGenericdataexception(implode("\n", $errors), 500);

		parent::display($tpl);
	}

	protected function getItems()
	{
		$items = $this->get('Items', $this->getName());

		$state = $this->get('State', $this->getName());

		if ((isset($state->enabledPlugin)) && ($state->enabledPlugin))
			foreach ($items as $item) $this->eventPlugin($item);

		return $items;
	}
}