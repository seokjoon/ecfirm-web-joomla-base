<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\Utilities\ArrayHelper;
use function strtoupper;

class EcListAdminController extends AdminController
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

		$this->registerTask('offenable', 'enable');
		$this->registerTask('onenable', 'enable');
	}

	public function bool($attr = 'enable')
	{
		$app = Factory::getApplication();

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$user = Factory::getUser();
		$ids = $this->input->get('cid', array(), 'array');
		$values = array(
			'off' . $attr => 2,
			'on' . $attr => 1
		);
		$task = $this->getTask();
		$value = ArrayHelper::getValue($values, $task, 2, 'int');

		foreach ($ids as $i => $id) {
			if (!($user->authorise('core.edit.state', 'com_' . EcConst::getPrefix() . $this->nameKey . '.' . $this->nameKey . '.' . (int) $id))) {
				unset($ids[$i]);
				$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'notice');
			}
		}

		if (empty($ids)) {
			$app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), 'error');
		} else {

			$model = $this->getModel($this->nameKey);

			if (! $model->bool($ids, $value, $attr)) {
				$app->enqueueMessage($model->getError(), 'error');
			} else {
				if ($value == 1)
					$this->setMessage(Text::plural(strtoupper($this->option) . '_ON_' . $attr . '_N', count($ids)));
				elseif ($value == 2)
					$this->setMessage(Text::plural(strtoupper($this->option) . '_OFF_' . $attr . '_N', count($ids)));
			}
		}
		$this->setRedirect('index.php?option=' . $this->option . '&view=' . $this->view_list);
	}

	public function enable()
	{
		$this->bool('enable');
	}

	public function getModel($name = '', $prefix = '', $config = array())
	{
		if (empty($name)) $name = $this->nameKey;

		return parent::getModel($name, $prefix, $config);
	}
}
