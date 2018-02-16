<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

abstract class EcItemModel extends ItemModel //BaseDatabaseModel
{

	protected $context;

	protected $nameCom;

	/**
	 * Constructor
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @since   3.0	BaseDatabaseModel
	 * @throws  \Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config = array());

		$this->context = $this->option . '.' . $this->name;
		$optionArray = explode('_', $this->option);
		$this->nameCom = $optionArray[1];
	}

	/**
	 * Method to test whether a record can be deleted.
	 * @param   object  $record  A record object.
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 * @since   1.6	AdminModel
	 */
	protected function canDelete($record)
	{
		$user = Factory::getUser();

		//return $user->authorise('core.delete', $this->option);
		$nameKey = $this->name;

		return $user->authorise('core.delete', $this->context . '.' . (int) $record->$nameKey);
	}

	/**
	 * Method to delete one or more records.
	 * @param   array  &$valueKeys  An array of record primary keys.
	 * @return  boolean  True if successful, false if an error occurs.
	 * @since   1.6 AdminModel
	 */
	public function delete(&$valueKeys)
	{
		$valueKeys = (array) $valueKeys;
		$table = $this->getTable();

		if ($this->getState('enabledPlugin', false)) {
			//$dispatcher = JEventDispatcher::getInstance();
			PluginHelper::importPlugin(EcConst::getPrefix());
		}

		foreach ($valueKeys as $i => $valueKey) {
			if (!($table->load($valueKey))) {
				$this->setError($table->getError());
				return false;
			}

			if (!($this->canDelete($table))) {
				unset($valueKeys[$i]);
				if ($this->getError()) $this->setError('canDelete false');
				else $this->setError(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
				return false;
			}

			if ($this->getState('enabledPlugin', false)) {
				$result = Factory::getApplication()->triggerEvent('on' . ucfirst($this->name) . 'BeforeDelete', array($this->context, $table));
				if (in_array(false, $result, true)) {
					$this->setError($table->getError());
					return false;
				}
			}

			if (!($table->delete($valueKey))) {
				$this->setError($table->getError());
				return false;
			}

			if ($this->getState('enabledPlugin', false))
				Factory::getApplication()->triggerEvent('on' . ucfirst($this->name) . 'BeforeDelete', array($this->context, $table));
		}

		$this->cleanCache();

		return true;
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
	 * Method to auto-populate the model state.
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 * @return  void
	 * @note    Calling getState in this method will result in recursion.
	 * @since   3.0	BaseDatabaseModel
	 */
	protected function populateState()
	{
		parent::populateState();
	}


	/**
	 * Method to save the form data.
	 * @param   array  $data  The form data.
	 * @return  boolean  True on success, False on error.
	 * @since   1.6 AdminModel
	 */
	public function save($data)
	{



	}
}