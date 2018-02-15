<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

abstract class EcFormController extends EcController //FormController
{

	public function __construct($config = array())
	{
		parent::__construct($config);

		if (!(isset($config['default_view'])))
			$this->default_view = $this->entity; //ex) examples or example(plural or singular)
	}

	/**
	 * Method to add a new record.
	 * @return  boolean  True if the record can be added, false if not.
	 * @since   1.6 FormController
	 * @Override
	 */
	public function add()
	{
		if (!($this->allowAdd())) {
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');

			$return = ($this->task != 'add') ? $this->getRedirectRequest() : $this->getRedirectReturn();
			$this->setRedirect($return); //($this->getRedirectRequest());
			$this->redirect();

			return false;
		}

		$this->setUserState('edit', 'data', null);

		$this->turnbackPush('edit');

		$layout = $this->input->get('layout', null, 'string');

		if (!(empty($layout))) $params['layout'] = $layout;
		$params['view'] = $this->nameKey;
		$params['task'] = $this->nameKey . '.editForm';

		$this->setRedirectParams($params);

		return true;
	}

	/**
	 * Method to check if you can add a new record.
	 * Extended classes can override this if necessary.
	 * @param   array  $data  An array of input data.
	 * @return  boolean
	 * @since   1.6 FormController
	 * @Override
	 */
	protected function allowAdd($data = array())
	{
		$user = Factory::getUser();

		return $user->authorise('core.create', $this->option) || count($user->getAuthorisedCategories($this->option, 'core.create'));
	}

	/**
	 * Method to check if you can edit an existing record.
	 * Extended classes can override this if necessary.
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 * @return  boolean
	 * @since   1.6 FormController
	 * @Override
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return Factory::getUser()->authorise('core.edit', $this->option);
	}

	/**
	 * Method to check if you can save a new or existing record.
	 * Extended classes can override this if necessary.
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 * @return  boolean
	 * @since   1.6 FormController
	 * @Override
	 */
	protected function allowSave($data, $nameKey = null)
	{
		if (empty($nameKey)) $nameKey = $this->nameKey;
		$recordId = isset($data[$nameKey]) ? $data[$nameKey] : '0';

		if ($recordId) return $this->allowEdit($data, $nameKey);
		else return $this->allowAdd($data);
	}

	/**
	 * Method to cancel an edit.
	 * @param   string  $key  The name of the primary key of the URL variable.
	 * @return  boolean  True if access level checks pass, false otherwise.
	 * @since   1.6 FormController
	 * @Override
	 */
	public function cancel($key = null)
	{

	}

}

