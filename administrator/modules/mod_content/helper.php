<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/models', 'ContentModel');

/**
 * Helper for mod_content
 *
 * @since  3.5
 */
abstract class ModContentHelper
{
	/**
	 * Get a list of articles.
	 *
	 * @param   \Joomla\Registry\Registry  &$params  The module parameters.
	 *
	 * @return  mixed  An array of articles, or false on error.
	 */
	public static function getList(&$params)
	{
		$user = JFactory::getuser();

		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set List SELECT
		$model->setState('list.select', 'a.id, a.title, a.checked_out, a.checked_out_time, ' .
			' a.access, a.created, a.created_by, a.created_by_alias, a.featured, a.state, a.hits');

		// Set Ordering filter
		$ordering  = $params->get('ordering', 'hits');
		$direction = $params->get('direction') ? 'ASC' : 'DESC';

		if (!in_array($ordering, array('hits', 'created', 'modified')))
		{
			$ordering = 'created';
		}

		if ($ordering == 'modified')
		{
			// Fall back to created if not yet modified
			$ordering = 'modified ' . $direction . ', a.created';
		}

		$model->setState('list.ordering', 'a.' . $ordering);
		$model->setState('list.direction', $direction);

		// Set Category Filter
		$categoryId = $params->get('catid');

		if (is_numeric($categoryId))
		{
			$model->setState('filter.category_id', $categoryId);
		}

		// Set User Filter.
		$userId = $user->get('id');

		switch ($params->get('authors'))
		{
			case 'by_me':
				$model->setState('filter.author_id', $userId);
				break;

			case 'not_me':
				$model->setState('filter.author_id', $userId);
				$model->setState('filter.author_id.include', false);
				break;
		}

		// Set the Start and Limit
		$model->setState('list.start', 0);
		$model->setState('list.limit', $params->get('count', 5));

		try
		{
			$items = $model->getItems();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// Set the links
		foreach ($items as &$item)
		{
			if ($user->authorise('core.edit', 'com_content.article.' . $item->id))
			{
				$item->link = 'index.php?option=com_content&task=article.edit&id=' . $item->id;
			}
			else
			{
				$item->link = '';
			}
		}

		return $items;
	}
}
