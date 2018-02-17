<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Model;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

abstract class EcListModel extends ListModel
{

	protected $keywords = array();

	/**
	 * Function to get the active filters
	 * @return  array  Associative array in the format: array('filter_published' => 0)
	 * @since   3.2 ListModel
	 */
	public function getActiveFilters()
	{
		return array(
			'filter_published' => 0
		); //return parent::getActiveFilters();
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 * @param   string  $id  An identifier string to generate the store id.
	 * @return  string  A store id.
	 * @since   1.6 ListModel
	 */
	protected function getStoreId($id = '')
	{
		if (! empty($this->keywords)) {
			foreach ($this->keywords as $keyword) {
				$id .= ':' . $this->getState('get.' . $keyword);
				$id .= ':' . $this->getState('filter.' . $keyword);
			}
		}

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 * @return  void ListModel
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication();
		$input = $app->input;

		try {
			foreach ($this->keywords as $keyword) {
				if (is_numeric($keyword)) $word = $input->getInt($keyword);
				else $word = $input->get($keyword);
				if ((!(empty($word))) || (is_numeric($word))) $this->setState('get.' . $keyword, $word);
			}
		} catch (Exception $e) {
			$this->setError($e->getMessage());
		}

		parent::populateState($ordering, $direction);
	}
}