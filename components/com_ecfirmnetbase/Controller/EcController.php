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

	protected function setViewModel($nameModel = null, $nameView = null)
	{
		$model = $this->getModel($nameModel);
		if (empty($nameView)) $nameView = $this->input->get('view');
		$layout = $this->input->get('layout', 'default', 'string');
		//$view = $this->getView($nameView, Factory::getDocument()->getType());
		$view = $this->getView($nameView, Factory::getDocument()->getType(), '', array('base_path' => $this->basePath, 'layout' => $layout));

		return $view->setModel($model);
	}

	protected function setUserState($task, $key, $value)
	{
		$context = $this->option . '.' . $task . '.' . $this->nameKey . '.' . $key;
		$app = Factory::getApplication();
		$app->setUserState($context, $value);
	}
}