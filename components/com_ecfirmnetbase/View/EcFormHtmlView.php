<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\View;

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

abstract class EcFormHtmlView extends EcHtmlView
{

	protected $form;

	public function editForm()
	{
		$layout = Factory::getApplication()->input->get('layout', null, 'string');

		$nameModelForm = (empty($layout)) ? $this->nameKey . 'form' : $layout . 'form';

		$this->form = $this->get('Form', $nameModelForm);

		parent::display(null);
	}

	public function useForm($layout = null)
	{
		if (empty($layout))
			$layout = Factory::getApplication()->input->get('layout', null, 'string') . 'form';
		if (empty($layout))
			$layout = $this->nameKey . 'form';

		$this->form = $this->get('Form', $layout); //EcDebug::lp($this->form, true);

		parent::display(null);
	}
}