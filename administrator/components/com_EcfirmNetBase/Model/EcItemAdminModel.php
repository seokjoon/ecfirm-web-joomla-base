<?php
/**
 * @package ecfirm.net
 */

use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;

class EcItemAdminModel extends AdminModel
{

	/**
	 * Abstract method for getting the form from the model.
	 * @param   array $data Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// TODO: Implement getForm() method.
	}
}