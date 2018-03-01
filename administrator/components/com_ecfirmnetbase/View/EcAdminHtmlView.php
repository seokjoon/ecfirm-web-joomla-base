<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\View;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use JViewGenericdataexception;

class EcAdminHtmlView extends BaseHtmlView
{

	protected $canDo; //JObject Object containing permissions for the item

	protected $plural;

	protected $state;

	/**
	 * Execute and display a template script.
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 * @return  mixed  A string if successful, otherwise an Error object.
	 * @see     \JViewLegacy::loadTemplate()
	 * @since   3.0 HtmlView
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');

		if (count($errors = $this->get('Errors')))
			throw new JViewGenericdataexception(implode("\n", $errors), 500);

		parent::display($tpl);
	}
}