<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

abstract class EcFormModel extends FormModel
{

	protected $context;

	protected $nameCom;

	protected $nameKey;

	/**
	 * Constructor
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @since   3.6 FormModel
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config = array());

		$this->nameKey = substr($this->name, 0, - 4);
		$this->context = $this->option . '.' . $this->nameKey;
		$optionArray = explode('_', $this->option);
		$this->nameCom = $optionArray[1];
	}

	/**
	 * Abstract method for getting the form from the model.
	 * @param   array $data Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 * @since   1.6 FormModel, AdminModel
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if ((!(isset($this->context))) || (empty($this->context)))
			$this->context = $this->option . '.' . $this->nameKey;

		$form = $this->loadForm($this->context, $this->nameKey, array(
			'control' => 'jform',
			'load_data' => $loadData
		));

		if (empty($form)) return false;

		//getState, setFieldAttribute

		return $form;
	}

	/**
	 * Method to get a single record.
	 * @param   integer  $valueKey  The id of the primary key.
	 * @return  \JObject|boolean  Object on success, false on failure.
	 * @since   1.6 AdminModel
	 */
	public function getItem($valueKey = null)
	{
		if ($valueKey == 0) $valueKey = $this->getState($this->name, 0);
		if ($valueKey == 0) $valueKey = Factory::getApplication()->input->get($this->name, 0, 'uint');
		if ($valueKey == 0) return false;

		$table = $this->getTable();
		$return = $table->load($valueKey);

		if (($return === false) && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		$properties = $table->getProperties(1);
		$item = ArrayHelper::toObject($properties, 'JObject');

		/* if (property_exists($item, 'options')) {
			$reg = new Registry($item->options);
			$item->options = $reg->toArray();
		} */

		return $item;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 * @return  Table  A Table object
	 * @since   3.0 BaseDatabaseModel
	 * @throws  \Exception
	 */
	public function getTable($name = '', $prefix = '', $options = array())
	{
		//if (empty($name)) $name = $this->name;
		//if(empty($prefix)) $prefix = $this->nameCom . 'Table';

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 * @return  array  The default data is an empty array.
	 * @since   1.6 FormModel
	 */
	protected function loadFormData()
	{
		$data = $this->getItem();
		$this->preprocessData($this->context, $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 * @return  void
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = '')
	{
		if (!($this->getState('enabledPlugin', false))) return;

		if(empty($group)) $group = EcConst::getPrefix();

		PluginHelper::importPlugin($group);
		Factory::getApplication()->triggerEvent('on' . $this->nameKey . 'PrepareData', array($context, $data));
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 * @param   \JForm  $form   A \JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 * @return  void
	 * @see     \JFormField
	 * @since   1.6 FormModel
	 * @throws  \Exception if there is an error in the form event.
	 */
	protected function preprocessForm(\JForm $form, $data, $group = '')
	{
		if (!($this->getState('enabledPlugin', false))) return;

		if(empty($group)) $group = EcConst::getPrefix();

		PluginHelper::importPlugin($group);
		Factory::getApplication()->triggerEvent('on' . $this->nameKey . 'PrepareForm', array($form, $data));
	}
}