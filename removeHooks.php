<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2015 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	$hooks = array(
		'integrate_pre_load' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::runTimeHooks#',
	);


foreach ($hooks as $hook => $function)
	remove_integration_function($hook, $function);
