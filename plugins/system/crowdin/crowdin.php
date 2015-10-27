<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Crowdin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * System - Crowdin Plugin
 *
 * @since  3.5
 */
class PlgSystemCrowdin extends JPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  3.3
	 */
	protected $app;

	/**
	 * Webhook for when a language is fully translated and approved.
	 * Triggers rebuilding the project.
	 *
	 * @return  void
	 */
	public function onAjaxSystemTriggerBuildProject()
	{
		$secret  = $this->params->get('secret');
		$project = $this->params->get('project');
		$apiKey  = $this->params->get('apikey');
		$log     = $this->params->get('log');

		if ($this->app->input->get('secret') != $secret)
		{
			return;
		}

		if (!$project || !$apiKey)
		{
			return;
		}

		$jhttp    = JHttpFactory::getHttp();
		$response = $jhttp->get('https://api.crowdin.com/api/project/' . $project . '/export?key=' . $apiKey);

		$response = $response;
		if ($log)
		{
			// Log the response
			JLog::addLogger(array('text_file' => 'crowdin.php'), JLog::ALL, array('crowdin'));
			JLog::add($response, JLog::INFO, 'crowdin');
		}

		return $response;
	}
}
