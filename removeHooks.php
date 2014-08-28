<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/OharaYTEmbed.php', // Kudos on requesting a file everywhere!
	'integrate_bbc_codes' => 'OYTE_bbc_add_code',
	'integrate_bbc_buttons' => 'OYTE_bbc_add_button',
	'integrate_general_mod_settings' => 'OYTE_settings',
	'integrate_menu_buttons' => 'OYTE_care', // Yes, a whole hook function for a copyright...
);

foreach ($hooks as $hook => $function)
	remove_integration_function($hook, $function);
