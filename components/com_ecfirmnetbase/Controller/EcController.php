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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

abstract class EcController extends BaseController
{

	protected $entity; //ex) examples or example(plural or singular)

	protected $option; //ex) com_ecexample

	protected $nameKey; //ex) example

	/**
	 * Constructor.
	 * @param   array                $config   An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 * @since   3.0 BaseController
	 * @Override
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$uri = Uri::getInstance();
		$this->option = $uri->getVar('option');
		$this->nameKey = str_replace('com_' . EcConst::getPrefix(), '', $this->option);
		//$this->nameKey = $this->getModel()->getTable()->getKeyName();
		$this->entity = $uri->getVar('view', $uri->getVar('task', $this->option));
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
	 * Removes an item.
	 * @return  boolean
	 * @since   1.6 AdminController
	 */
	public function delete()
	{
		$model = $this->getModel();

		try {
			$valueKeys = $this->input->get($this->nameKey, array(), 'array');
			if(empty($valueKeys)) {
				$jform = $this->input->post->get('jform', array(), 'array');
				$valueKeys = array($jform[$this->nameKey]);
			}
			if(!(is_array($valueKeys)) || count($valueKeys) < 1)
				throw new Exception();

			ArrayHelper::toString($valueKeys);

			if($model->delete($valueKeys))
				$this->setMessage(Text::plural($this->option . '_' . $this->entity . '_N_ITEMS_DELETED', count($valueKeys)));
			else throw new Exception($model->getError());
		} catch (Exception $e) {
			$this->setMessage($e->getMessage(), 'error');
			return false;
		}

		$this->setMessage(Text::_($this->option . '_' . $this->nameKey . '_DELETE_SUCCESS'));

		return true;
	}

	protected function getItem($valueKey = 0, $nameKey = null)
	{
		if (empty($nameKey)) $nameKey = $this->nameKey;
		if ($valueKey == 0) $valueKey = $this->input->get($nameKey, '0', 'uint');
		$model = $this->getModel($nameKey);

		return $model->getItem($valueKey);
	}

	protected function getItems($name = null)
	{
		if (empty($name)) $name = $this->entity;
		$model = $this->getModel($name);

		return $model->getItems();
	}

	/**
	 * Method to get a model object, loading it if required.
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 * @return  BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
	 * @since   3.0 BaseController
	 * @Override
	 */
	public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		if (empty($name)) $name = $this->entity;

		return parent::getModel($name, $prefix, $config);
	}

	protected function getRedirectLogin()
	{
		$return = $this->getRedirectRequest();
		$prefix = EcConst::getPrefix();

		return 'index.php?option=com_' . $prefix . 'user&view=login&return=' . $return;
	}

	protected function getRedirectRequest()
	{
		$request = Uri::getInstance()->toString();

		if (empty($request) || ! Uri::isInternal($request))
			return $this->getRedirectReturn();
		else
			return $request;
	}

	protected function getRedirectReturn()
	{
		$return = $this->input->get('return', null, 'base64');

		//if(empty($return) || !JUri::isInternal(base64_decode($return)))
		if (empty($return))
			return Uri::base();
		else
			return base64_decode($return);
	}

	protected function getUserState($task, $key, $default)
	{
		$context = $this->option . '.' . $task . '.' . $this->nameKey . '.' . $key;
		$app = Factory::getApplication();

		return $app->getUserState($context, $default);
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
				throw new Exception('TODO modelForm empty error'); //TODO
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
				throw new Exception('TODO modelForm valid error'); //TODO
			}
			////////

			if (!($model->save($validData))) {
				$this->setUserState('edit', 'data', $validData);
				throw new Exception(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			}
		} catch (Exception $e) {
			$app->enqueueMessage($e->getMessage(), 'error');
			$this->setMessage(strtoupper(Text::_($this->option . '_' . $this->nameKey . '_SAVE_FAILURE')), 'error');
			return false;
		}

		$this->setMessage(strtoupper(Text::_($this->option . '_' . $this->nameKey . '_SAVE_SUCCESS')));
		$this->setUserState('edit', 'data', null);

		return true;
	}

	protected function setRedirectParams($params = array())
	{
		$prefix = 'index.php';
		$option = (isset($params['option'])) ? '?option=com_' . $params['option'] : $option = '?option=com_' . $this->name;
		$view = (isset($params['view'])) ? '&view=' . $params['view'] : '&view=' . ($this->input->get('view', null, 'string'));
		$task = (isset($params['task'])) ? '&task=' . $params['task'] : '';
		$key = ((isset($params['nameKey'])) && (isset($params['valueKey']))) ? '&' . $params['nameKey'] . '=' . $params['valueKey'] : '';
		$format = (isset($params['format'])) ? '&format=' . $params['format'] :
			'&format=' . ($this->input->get('format', 'html', 'string'));
		$layout = (isset($params['layout'])) ? $params['layout'] : $this->input->get('layout', null, 'string');
		$layout = (empty($layout)) ? '' : '&layout=' . $layout;
		$tmpl = (isset($params['tmpl'])) ? $params['tmpl'] : $this->input->get('tmpl', null, 'string');
		$tmpl = (empty($tmpl)) ? '' : '&tmpl=' . $tmpl;
		$itemId = (isset($params['itemId'])) ? $params['itemId'] : EcUrl::getItemId();
		$itemId = ($itemId > 0) ? '&Itemid=' . $itemId : '';
		$etc = (isset($params['etc'])) ? '&' . $params['etc'] : '';
		$url = (isset($url)) ? $params['url'] : $prefix . $option . $view . $task . $key . $format . $layout . $tmpl . $itemId . $etc;
		$msg = (isset($params['msg'])) ? $params['msg'] : null;
		$type = (isset($params['type'])) ? $params['type'] : null;
		//EcDebug::lp($url, 'url'); EcDebug::lp($msg, 'msg'); EcDebug::lp($type, 'type');

		$this->setRedirect($url, $msg, $type); //$this->setRedirect(JRoute::_($url));
		//$this->redirect();
	}

	protected function setUserState($task, $key, $value)
	{
		$context = $this->option . '.' . $task . '.' . $this->nameKey . '.' . $key;
		$app = Factory::getApplication();
		$app->setUserState($context, $value);
	}

	protected function setViewModel($nameModel = null, $nameView = null)
	{
		$model = $this->getModel($nameModel);
		if (empty($nameView)) $nameView = $this->input->get('view');
		$layout = $this->input->get('layout', 'default', 'string');
		//$view = $this->getView($nameView, Factory::getDocument()->getType());
		$view = $this->getView($nameView, Factory::getDocument()->getType(), '', array('base_path' => $this->basePath, 'layout' => $layout));

		return $view->setModel($model);
	}
}