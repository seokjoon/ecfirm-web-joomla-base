<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcConst;
use Joomla\Component\EcfirmNetBase\Site\Helper\EcDebug;
use function str_replace;

defined('_JEXEC') or die;

abstract class EcController extends BaseController
{

	protected $entity; //ex) examples or example(plural or singular)

	protected $option; //ex) com_ecexample

	protected $nameKey; //ex) example

	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		$uri = Uri::getInstance();
		$this->option = $uri->getVar('option');
		$this->nameKey = str_replace('com_' . EcConst::getPrefix(), '', $this->option);
		$this->entity = $uri->getVar('view', $uri->getVar('task', $this->option));
	}
}