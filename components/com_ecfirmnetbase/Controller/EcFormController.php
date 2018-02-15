<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Controller;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

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
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return Factory::getUser()->authorise('core.edit', $this->option);
	}

	/**
	 * Method to check if you can save a new or existing record.
	 * Extended classes can override this if necessary.
	 * @param   array   $data  An array of input data.
	 * @param   string  $nameKey   The name of the key for the primary key.
	 * @return  boolean
	 * @since   1.6 FormController
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
	 * @param   string  $nameKey  The name of the primary key of the URL variable.
	 * @return  boolean  True if access level checks pass, false otherwise.
	 * @since   1.6 FormController
	 */
	public function cancel($nameKey = null)
	{
		$this->setUserState('edit', 'data', null);

		return true;
	}

	/**
	 * Removes an item.
	 * @return  void
	 * @since   1.6 AdminController
	 */
	public function delete()
	{
		//Session::checkToken() or die(Text::_('JINVALID_TOKEN'));
		((Session::checkToken()) || (Session::checkToken('get'))) or die(Text::_('JINVALID_TOKEN'));

		$model = $this->getModel();

		try {
			$valueKeys = $this->input->get($this->nameKey, array(), 'array');
			if (empty($valueKeys)) {
				$jform = $this->input->post->get('jform', array(), 'array');
				$valueKeys = array($jform[$this->nameKey]);
			}
			if (!(is_array($valueKeys)) || count($valueKeys) < 1)
				throw new Exception();

			ArrayHelper::toString($valueKeys);

			if ($model->delete($valueKeys))
				$this->setMessage(Text::plural($this->option . '_' . $this->entity . '_N_ITEMS_DELETED', count($valueKeys)));
			else throw new Exception();
		} catch (Exception $e) {
			$this->setMessage($model->getError(), 'error');
			$this->setRedirect($this->getRedirectRequest());
			$this->redirect();
		}

		$this->postDeleteHook($model, $valueKeys);

		$params['view'] = $this->nameKey . 's';
		$params['msg'] = Text::_($this->option . '_' . $this->nameKey . '_DELETE_SUCCESS');
		$this->setRedirectParams($params);
	}

	/**
	 * Method to edit an existing record.
	 * @param   string  $nameKey     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *                           (sometimes required to avoid router collisions).
	 * @return  boolean  True if access level check and checkout passes, false otherwise.
	 * @since   1.6 FormController
	 */
	public function edit($nameKey = null, $urlVar = null)
	{
		// Do not cache the response to this, its a redirect, and mod_expires and google chrome browser bugs cache it forever!
		Factory::getApplication()->allowCache(false);

		if (empty($nameKey)) $nameKey = $this->nameKey;
		$valueKey = $this->input->get($nameKey, 0, 'uint');

		if (!($this->allowEdit(array($nameKey => $valueKey), $nameKey))) {
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

			$return = ($this->task != 'edit') ? $this->getRedirectRequest() : $this->getRedirectReturn();
			$this->setRedirect($return); //($this->getRedirectRequest());
			$this->redirect();

			return false;
		}

		$this->setUserState('edit', 'data', null);

		$this->turnbackPush('edit');

		$params['nameKey'] = $nameKey;
		$params['valueKey'] = $valueKey;
		$params['view'] = $nameKey;
		$params['task'] = $nameKey . '.editForm'; //EcDebug::log($params, __method__);

		$this->setRedirectParams($params);

		return true;
	}



	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 * @param   BaseDatabaseModel $model  The data model object.
	 * @param   integer        $id     The validated data.
	 * @return  void
	 * @since   3.1 AdminController
	 */
	protected function postDeleteHook(BaseDatabaseModel $model, $id = null)
	{
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 * @param   BaseDatabaseModel  $model      The data model object.
	 * @param   array              $validData  The validated data.
	 * @return  void
	 * @since   1.6 FormController
	 */
	protected function postSaveHook(BaseDatabaseModel $model, $validData = array())
	{
	}

	/**
	 * Method to save a record.
	 * @param   string  $nameKey     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 * @return  boolean  True if successful, false otherwise.
	 * @since   1.6 FormController
	 */
	public function save($nameKey = null, $urlVar = null)
	{
		((Session::checkToken()) || (Session::checkToken('get'))) or die(Text::_('JINVALID_TOKEN'));

		if (empty($nameKey)) $nameKey = $this->nameKey;
		$app = Factory::getApplication();
		$model = $this->getModel();
		$data = $this->input->post->get('jform', array(), 'array'); //EcDebug::log($data);

		try {

			if (!($this->allowSave($data, $nameKey)))
				throw new Exception(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));

			////////
			if (($nameKey != 'user') && (isset($data['user'])))
				$data['user'] = Factory::getUser()->id; //FIXME
			////////

			////////
			$layout = $app->input->get('layout', null, 'string');

			$nameModelForm = (empty($layout)) ? $this->entity . 'form' : $layout . 'form';
			$modelForm = $this->getModel($nameModelForm);

			$form = $modelForm->getForm($data, false);
			if (!($form)) {
				$app->enqueueMessage($modelForm->getError(), 'error');
				throw new Exception('TODO modelForm empty error'); //FIXME
			}

			$validData = $modelForm->validate($form, $data); //EcDebug::log($validData);
			if ($validData === false) {
				$errors = $modelForm->getErrors();
				//Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i ++)
					if ($errors[$i] instanceof Exception)
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					else $app->enqueueMessage($errors[$i], 'warning');
				$this->setUserState('edit', 'data', $data);
				throw new Exception('TODO modelForm valid error'); //FIXME
			}
			////////

			if (!($model->save($validData))) {
				$this->setUserState('edit', 'data', $validData);
				throw new Exception(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			}
		} catch (Exception $e) {
			$this->setMessage($e->getMessage(), 'error');
			return false;
		}

		$postfix = ($data[$nameKey] == 0) ? '_SUBMIT' : '_SAVE_SUCCESS';
		$msg = strtoupper($this->option) . $postfix;

		$this->setMessage(Text::_($msg));
		$this->setUserState('edit', 'data', null);

		$this->postSaveHook($model, $validData);

		return true;
	}

}

