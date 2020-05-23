<?php
/**
 ************************************************************************
 Source Check - module that verifies the integrity of Joomla files
 ************************************************************************
 * @author    Maciej Bednarski (Green Line) <maciek.bednarski@gmail.com>
 * @copyright Copyright (C) 2020 Green Line. All Rights Reserved.
 * @license   GNU General Public License version 3, or later
 * @version   HEAD
 ************************************************************************
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

include_once (JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_srccheck'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'srcchecks.php');

class PlgSystemSrcCheckVerify extends JPlugin
{
//	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config)
	{
		// Calling the parent Constructor
		parent::__construct($subject, $config);
	}
	/**
	 * After Initialise Event.
	 * Checks if URL exists in cache, if so dumps it directly and closes.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onAfterInitialise()
	{
 	}

	/**
	 * After Render Event.
	 * Verify if current page is not excluded from cache.
	 *
	 * @return   void
	 *
	 * @since   3.9.12
	 */
	public function onAfterRender()
	{
	}

	/**
	 * After Respond Event.
	 * Stores page in cache.
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public function onAfterRespond()
	{
            echo "PlgSystemSrcCheckVerify [After respond]: START<br>";
            $pid = pcntl_fork();
            echo "PlgSystemSrcCheckVerify [After respond]: pid = " . $pid . "<br>";
            
            $ver = new SrcCheckControllerSrcChecks;
            $ver->verify(SILENCE_MODE );
            echo "PlgSystemSrcCheckVerify [After respond]: STOP<br>";
        }
} 