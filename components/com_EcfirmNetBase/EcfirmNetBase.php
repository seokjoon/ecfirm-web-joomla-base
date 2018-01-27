<?php
/**
 * @package    EcfirmNetBase
 *
 * @author     m <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

$controller = BaseController::getInstance('EcfirmNetBase');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
