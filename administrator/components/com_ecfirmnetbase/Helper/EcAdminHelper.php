<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Administrator\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

defined('_JEXEC') or die;

class EcAdminHelper extends ContentHelper
{

	/**
	 * Build an array of block/unblock user states to be used by jgrid.state,
	 * State options will be different for any user
	 * and for currently logged in user
	 * @param   boolean  $self  True if state array is for currently logged in user
	 * @return  array  a list of possible states to display
	 * @since  3.0 JHtmlUsers(com_users admin)
	 */
	public static function bool($attr = 'enable')
	{
		$states = array(
			1 => array(
				'task' => 'off' . $attr,
				'text' => '',
				'active_title' => 'COM_EC_OFF_DESC',
				'inactive_title' => '',
				'tip' => true,
				'active_class' => 'publish',
				'inactive_class' => 'publish'
			),
			0 => array(
				'task' => 'on' . $attr,
				'text' => '',
				'active_title' => 'COM_EC_ON_DESC',
				'inactive_title' => '',
				'tip' => true,
				'active_class' => 'checkin',
				'inactive_class' => 'checkin'
			)
		);

		return $states;
	}

	public static function getActionsEc($id = null, $section = 'component')
	{
		$user = Factory::getUser();
		$result = new CMSObject();

		$path = JPATH_COMPONENT_ADMINISTRATOR . '/access.xml';

		if ($section == 'component') $assetName = $id;
		else $assetName = $id . '.' . $section;

		if (isset($id) && ! empty($id)) $assetName .= '.' . $id;

		$actions = Access::getActionsFromFile($path, "/access/section[@name='" . $section . "']/");

		foreach ($actions as $action)
			$result->set($action->name, $user->authorise($action->name, $assetName));

		return $result;
	}
}