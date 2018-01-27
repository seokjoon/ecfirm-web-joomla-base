<?php
/**
 * @package ecfirm.net
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_users
 *
 * @since  4.0.0
 */
class EcfirmNetBaseDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\EcfirmNetBase';
}
