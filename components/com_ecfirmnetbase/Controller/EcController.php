<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;

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
		$this->entity = $uri->getVar('view', $uri->getVar('task', $this->option));
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