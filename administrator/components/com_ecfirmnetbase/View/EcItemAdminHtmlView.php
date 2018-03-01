<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\View;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\EcfirmNetBase\Administrator\Helper\EcAdminHelper;
use JViewGenericdataexception;

defined('_JEXEC') or die;

class EcItemAdminHtmlView extends EcAdminHtmlView
{

	protected $form;

	protected $item;

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->plural = false;
	}

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
		$this->canDo = EcAdminHelper::getActionsEc($this->getName());

		if (count($errors = $this->get('Errors')))
			throw new JViewGenericdataexception(implode("\n", $errors), 500);

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return  void
	 * @since   1.6 FormView
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		ToolbarHelper::cancel($this->getName() . '.cancel', 'JTOOLBAR_CLOSE');
		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_CONTENT_ARTICLE_MANAGER_EDIT');
	}
}