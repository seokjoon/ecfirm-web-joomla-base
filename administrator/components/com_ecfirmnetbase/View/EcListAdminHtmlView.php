<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\View;

use JHtmlSidebar;
use JLoader;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\EcfirmNetBase\Administrator\Helper\EcAdminHelper;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\String\StringHelper;
use JViewGenericdataexception;

defined('_JEXEC') or die;

class EcListAdminHtmlView extends EcAdminHtmlView
{

	protected $items;

	protected $option;

	protected $pagiantion;

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->plural = true;
	}

	public function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();

			//$helper = EcConst::getPrefix() . 'Helper' . ucfirst(substr($this->getName(), 0, - 1));
			JLoader::discover('', JPATH_COMPONENT_ADMINISTRATOR . '/helpers');
			$helper = ucfirst(EcConst::getPrefix()) . ucfirst(substr($this->getName(), 0, - 1));

			$helper::addSubmenu($this->getName());

			$this->sidebar = JHtmlSidebar::render();
		}

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors')))
			throw new JViewGenericdataexception(implode("\n", $errors), 500);

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @return  void
	 * @since   1.6 ListView
	 */
	protected function addToolbar()
	{
		$app = Factory::getApplication();

		$this->option = $app->input->get('option', 'com_' . EcConst::getPrefix());
		$this->canDo = EcAdminHelper::getActionsEc($this->option);

		ToolbarHelper::title(Text::_(StringHelper::strtoupper($this->option)), 'stack article');

		if ($this->canDo->get('core.admin')) {
			ToolbarHelper::divider();
			ToolbarHelper::preferences($this->option);
		}
	}
}