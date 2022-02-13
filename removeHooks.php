<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.2.12
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (C) 2022 Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
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
	'integrate_load_theme' => 'OYTE_css',
);

foreach ($hooks as $hook => $function)
	remove_integration_function($hook, $function);
