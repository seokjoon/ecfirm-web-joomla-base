<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Model;

use Joomla\CMS\MVC\Model\FormModel;

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
	 * @since   1.6 FormModel
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// TODO: Implement getForm() method.
	}
}