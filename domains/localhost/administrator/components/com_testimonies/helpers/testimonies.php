<?php
/**
 * @version		$Id: testimonies.php $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		kiennh
 * This component was generated by http://xipat.com/ - 2015
 */

defined('_JEXEC') or die;

/**
 * testimonies component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_testimonies
 * @since		1.6
 */
class TestimoniesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		
			JSubMenuHelper::addEntry(
				JText::_('COM_TESTIMONIES_SUBMENU_ITEMS'),
				'index.php?option=com_testimonies&view=items',
				$vName == 'items'
			);

		
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId)) {
			$assetName = 'com_testimonies';
		} else {
			$assetName = 'com_testimonies.category.'.(int) $categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
}
