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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcFile;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcFileImg;
use Joomla\Registry\Registry;
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
	 * @return  boolean
	 * @since   1.6 AdminController
	 */
	public function delete()
	{
		((Session::checkToken()) || (Session::checkToken('get'))) or die(Text::_('JINVALID_TOKEN'));

		$bool = parent::delete();

		if ($bool) {
			$params['view'] = $this->nameKey . 's';
			$params['msg'] = Text::_($this->option . '_' . $this->nameKey . '_DELETE_SUCCESS');
			$this->setRedirectParams($params);
		} else {
			$this->setRedirect($this->getRedirectRequest());
			$this->redirect();
		}

		return $bool;
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

	public function editForm()
	{
		////////internal redirect check
		$prev = $this->getUserState('edit', 'turnback', null); //EcDebug::lp($prev, true);
		if (((empty($prev))) || (!(Uri::isInternal($prev))))
			jexit(Text::_('JLIB_APPLICATION_ERROR_UNHELD_ID'));
		////////

		$view = $this->getView($this->default_view, Factory::getDocument()->getType(), '', array(
			'layout' => 'edit'
		));

		$view->setModel($this->getModel($this->nameKey));
		$view->setModel($this->getModel($this->nameKey . 'form'));
		$view->editForm();
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

		$bool = parent::save($nameKey, $urlVar);

		$msgPostfix = ($bool) ? 'SUCCESS' : 'FAILURE';
		$this->setMessage(strtoupper(Text::_($this->option . '_' . $this->nameKey . '_SAVE_' . $msgPostfix)));

		$this->turnbackPop('edit');

		return $bool;
	}

	/**
	 * @deprecated
	 */
	protected function saveFile()
	{
		$files = $this->input->files->get('jform');
		if ($files['file']['error'] != 0)
			return false;
		$jform = $this->input->post->get('jform', array(), 'array');

		//$files = EcFile::setFileByUser($files['file'], $this->nameKey);
		$files = EcFile::setFileByName($files['file'], $this->nameKey);

		$jform['files'] = json_encode($files, JSON_UNESCAPED_SLASHES);

		$this->input->post->set('jform', $jform);

		return true;
	}

	/**
	 * @deprecated
	 */
	protected function saveFileImg($nameCol = 'img', $ratio = false)
	{
		$files = $this->input->files->get('jform'); //EcDebug::lp($files, true);
		if($files[$nameCol]['error'] != 0) return false;

		$jform = $this->input->post->get('jform', array(), 'array');

		$files[$nameCol]['ratio'] = $ratio; //if($isRatio) $files[$nameCol]['ratio'] = $isRatio;
		$imgs = (array) EcFileImg::setFileImgByName($files[$nameCol], $this->nameKey, $nameCol); //EcDebug::lp($imgs); jexit();

		//$jform['imgs'] = json_encode($imgsArray, JSON_UNESCAPED_SLASHES);
		$reg = new Registry();
		if(!(empty($jform['imgs']))) $reg->loadString($jform['imgs']);
		$reg->loadArray($imgs);
		$jform['imgs'] = stripslashes($reg->toString()); //EcDebug::log($jform);

		$this->input->post->set('jform', $jform);

		return true;
	}

	/**
	 * @deprecated
	 */
	protected function saveFileImgs($nameCol = 'img', $ratio = false)
	{
		$files = $this->input->files->get('jform'); //EcDebug::log('----'); EcDebug::log(json_encode($files), __method__);
		if (count($files) == 0) return false;
		$imgs = array();

		foreach ($files[$nameCol] as &$file) { //EcDebug::log($file, __method__);
			if(((strpos($file['type'], 'image')) === false) || ($file['error'] != 0)) return false;
			$file['ratio'] = $ratio;
			array_push($imgs, EcFileImg::setFileImgByName($file, $this->nameKey, $nameCol));
		} //EcDebug::log(json_encode($imgs), __method__);

		$jform = $this->input->post->get('jform', array(), 'array'); //EcDebug::log(json_encode($jform), __method__);
		$reg = new Registry();
		if (!(empty($jform['imgs']))) $reg->loadString($jform['imgs']);
		$reg->loadArray($imgs);
		$jform['imgs'] = stripslashes($reg->toString()); //EcDebug::log($jform['imgs'], __method__);

		$this->input->post->set('jform', $jform);

		return true;
	}

	/**
	 * @deprecated
	 */
	protected function saveFileImgsLegacy()
	{
		$files = $this->input->files->get('jform'); //EcDebug::lp($files, true);
		if (count($files) == 0) return false;

		$jform = $this->input->post->get('jform', array(), 'array');

		$imgs = array();
		foreach ($files as $nameCol => $file) { //EcDebug::lp($file, true);
			if ((strpos($file['type'], 'image')) === false) continue;
			if ($file['error'] != 0) continue;

			$outs = EcFileImg::setFileImgByName($file, $this->nameKey, $nameCol);

			foreach ($outs as $k => $out) $imgs[$k] = $out;
		}

		$reg = new Registry();
		if (!(empty($jform['imgs']))) $reg->loadString($jform['imgs']);
		$reg->loadArray($imgs);
		$jform['imgs'] = stripslashes($reg->toString()); //EcDebug::log($jform);

		$this->input->post->set('jform', $jform);

		return true;
	}

	protected function turnbackPop($task = null)
	{
		if (empty($task)) $task = $this->task;
		$turnback = $this->getUserState($task, 'turnback', null);

		$this->setUserState($task, 'turnback', null);

		if ($turnback == Uri::getInstance()->toString())
			$turnback = Uri::base(); //avoid inifite loop

		if (!(empty($turnback))) $this->setRedirect($turnback);
	}

	protected function turnbackPush($task = null, $url = null)
	{
		if(empty($task)) $task = $this->task;

		if(empty($url)) $url = Uri::getInstance()->toString();

		$this->setUserState($task, 'turnback', $url);
	}

	public function useForm($layout = null)
	{
		//TODO internal redirect check
		if(empty($layout)) $layout = $this->nameKey;

		$view = $this->getView($layout, Factory::getDocument()->getType(), '', array(
			'layout' => $layout . 'form'
		));

		//$view->setModel($this->getModel($this->nameKey));
		$view->setModel($this->getModel($layout . 'form')); //EcDebug::lp($view);
		$view->useForm($layout . 'form');
	}
}

