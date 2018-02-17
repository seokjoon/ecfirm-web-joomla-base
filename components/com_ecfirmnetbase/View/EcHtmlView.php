<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\View;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use stdClass;

abstract class EcHtmlView extends BaseHtmlView
{

	protected $context;

	protected $item;

	protected $optionCom;

	protected $nameKey;

	//protected $params;

	protected $plural;

	/**
	 * Constructor
	 * @param   array  $config  A named configuration array for object construction.
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).
	 *                          charset: the character set to use for display
	 *                          escape: the name (optional) of the function to use for escaping strings
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
	 *                          layout: the layout (optional) to use to display the view
	 * @since   3.0 HtmlView
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->optionCom = Factory::getApplication()->input->get('option');
	}

	/**
	 * Execute and display a template script.
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @return  mixed  A string if successful, otherwise an Error object.
	 * @see     \JViewLegacy::loadTemplate()
	 * @since   3.0 HtmlView
	 */
	public function display($tpl = null)
	{
		$this->context = $this->optionCom . '.' . $this->nameKey;
		//$this->params = Factory::getApplication()->getParams(); //$this->get('State')->get('params);

		parent::display($tpl);
	}


	protected function eventPlugin(&$item)
	{
		$item->event = new stdClass();

		PluginHelper::importPlugin(EcConst::getPrefix());

		$results = Factory::getApplication()->triggerEvent('on' . ucfirst($this->nameKey) . 'BeforeDisplay', array($this->context, &$item, &$item->params)); //TODO: $offset
		$item->event->beforeDisplay = trim(implode("\n", $results));

		$results = Factory::getApplication()->triggerEvent('on' . ucfirst($this->nameKey) . 'AfterDisplay', array($this->context, &$item, &$item->params)); //TODO: $offset
		$item->event->afterDisplay = trim(implode("\n", $results));

		return $item;
	}
}