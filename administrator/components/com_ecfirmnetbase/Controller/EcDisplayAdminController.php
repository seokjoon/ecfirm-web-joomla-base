<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;

defined('_JEXEC') or die;

class EcDisplayAdminController extends BaseController
{

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
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$uri = Uri::getInstance();
		$this->option = $uri->getVar('option');
		$this->nameKey = str_replace('com_' . EcConst::getPrefix(), '', $this->option);
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 * @return  boolean  True if the ID is in the edit list.
	 * @since   3.0 BaseController
	 * XX: ec에서는 엔티티명을 키로 사용하나 joomla backend에서는 'id'를 키로 사용
	 */
	protected function checkEditId($context, $id)
	{
		if (!($id)) return true; //jexit($context.':'.$id.':'.$this->nameKey);

		$app = Factory::getApplication();
		$values = (array) $app->getUserState($context . '.' . $this->nameKey);
		$result = in_array((int) $id, $values['id']);

		return $result;
	}

	public function display($cachable = false, $urlparams = false)
	{
		$defaultView = $this->nameKey . 's';
		$view = $this->input->get('view', $defaultView);
		$this->input->set('view', $view);

		$layout = $this->input->get('layout', '');
		$key = $this->input->getInt($this->nameKey);
		if (($layout == 'edit') && (! ($this->checkEditId($this->option . '.edit', $key)))) {
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $key), 'error');
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $defaultView, false));
			return false;
		}

		return parent::display();
	}
}