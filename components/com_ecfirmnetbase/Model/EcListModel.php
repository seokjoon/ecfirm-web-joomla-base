<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Model;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcDebug;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

abstract class EcListModel extends ListModel
{

	protected $keywords = array();

	/**
	 * Constructor
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @since   1.6 ListModel
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		$config['ignore_request'] = false;

		parent::__construct($config);
	}

	/**
	 * Function to get the active filters
	 * @return  array  Associative array in the format: array('filter_published' => 0)
	 * @since   3.2
	 */
	/* public function getActiveFilters()
	{
		return array(
			'filter_published' => 0
		); //return parent::getActiveFilters();
	} */

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

	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{

		/* EcDebug::lp($name . ':' . $source);
		EcDebug::lp($options);
		EcDebug::lp($clear, $xpath); */

		// Handle the optional arguments.
		$options['control'] = ArrayHelper::getValue((array) $options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (!$clear && isset($this->_forms[$hash]))
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		\JForm::addFormPath(JPATH_COMPONENT . '/forms');
		\JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		\JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try
		{
			$form = \JForm::getInstance($name, $source, $options, false, $xpath);

			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}

			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);

			// Load the data into the form after the plugins have operated.
			$form->bind($data);
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}


		EcDebug::lp($data);
		//EcDebug::lp($form);


		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = \JFactory::getApplication()->getUserState($this->context, new \stdClass);

		//EcDebug::lp($data);

		//$data->list['limit'] = 5;
		//$data->limitstart=10;

		// Pre-fill the list options
		if (!property_exists($data, 'list'))
		{
			$data->list = array(
				'direction' => $this->getState('list.direction'),
				'limit'     => $this->getState('list.limit'),
				'ordering'  => $this->getState('list.ordering'),
				'start'     => $this->getState('list.start'),
			);
		}

		//EcDebug::lp($data);

		return $data;
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

		EcDebug::lp(__METHOD__);

		try {
			foreach ($this->keywords as $keyword) {

				EcDebug::lp($keyword);

				if (is_numeric($keyword)) $word = $input->getInt($keyword);
				else $word = $input->get($keyword);
				if ((!(empty($word))) || (is_numeric($word))) {
					$this->setState('get.' . $keyword, $word);

					EcDebug::lp($word);

				}
			}
		} catch (Exception $e) {
			$this->setError($e->getMessage());
		}

		parent::populateState($ordering, $direction);
	}
}