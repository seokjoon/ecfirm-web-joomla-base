<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

class EcItemAdminModel extends AdminModel
{

	/**
	 * @param array $pks
	 * @param int $value
	 * @param string $attr
	 * @return bool
	 * @throws \Exception
	 */
	public function bool($pks = array(), $value = 0, $attr = 'display')
	{
		$pks = (array) $pks;
		ArrayHelper::toInteger($pks); //JArrayHelper::toInteger($pks);
		if (empty($pks)) {
			$this->setError(Text::_('COM_BM_NO_ITEM_SELECTED'));
			return false;
		} //EcDebug::log($pks, __method__); //EcDebug::log($value.':'.$attr, __method__);

		$table = $this->getTable(); //EcDebug::log($table, __method__);
		$user = Factory::getUser();

		foreach ($pks as $pk) {
			if (! $user->authorise('core.edit', 'com_' . EcConst::getPrefix() . $this->name . '.' . $this->name . '.' . $pk)) {
				$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));
				return false;
			}

			if (! $table->load($pk)) {
				if ($error = $table->getError()) { // Fatal error
					$this->setError($error);
					return false;
				} else { // Not fatal error
					$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			$table->$attr = $value;

			if (! $table->check()) { //Check the row
				$this->setError($table->getError());
				return false;
			}

			if (! $table->store()) { //Store the row
				$this->setError($table->getError());
				return false;
			}
		}

		$this->cleanCache();

		return true;
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
	 * @since   1.6 FormModel, ArticleModel
	 */
	protected function loadFormData()
	{
		return $this->getItem();
	}
}