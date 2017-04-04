<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors-xtd.fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Fieldlayout Field class for the fields plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class  JFormFieldFieldlayout extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Fieldlayout';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @throws  \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		$options = parent::getOptions();

		// Get Templates
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('e.element, e.name')
			->from('#__extensions as e')
			->where('e.client_id = 0')
			->where('e.type = ' . $db->quote('template'))
			->where('e.enabled = 1');
		$db->setQuery($query);
		$templates = $db->loadObjectList('element');

		$lang       = JFactory::getLanguage();
		$layoutpath = '/html/layouts/com_fields/field';

		foreach ($templates as $template)
		{
			$template_path = JPath::clean(JPATH_ROOT . '/templates/' . $template->element . $layoutpath);

			if (is_dir($template_path))
			{
				$files = JFolder::files($template_path, '^[^_]*\.php$', false, true);

				foreach ($files as $key => &$value)
				{
					$value = basename($value, '.php');

					if ($value == 'render')
					{
						unset($files[$key]);
					}
				}

				if ($files)
				{
					// Load template language file
					$lang->load('tpl_' . $template->element . '.sys', JPATH_SITE)
					|| $lang->load('tpl_' . $template->element . '.sys',  JPATH_SITE . '/templates/' . $template->element);

					$templatename = JText::sprintf('JOPTION_FROM_TEMPLATE', JText::_($template->name));
					array_unshift($files, JHtml::_('select.optgroup', $templatename));
					array_push($files, JHtml::_('select.optgroup', $templatename));
					$options = array_merge ($options, $files);
				}
			}
		}

		return $options;
	}
}
