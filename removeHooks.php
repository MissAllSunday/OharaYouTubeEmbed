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
	'integrate_bbc_codes' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::code#',
	'integrate_bbc_buttons' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::button#',
	'integrate_general_mod_settings' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::settings#',
	'integrate_credits' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::who#',
	'integrate_pre_parsebbc' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::autoEmbed#',
	'integrate_load_theme' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::css#',
);

foreach ($hooks as $hook => $function)
	remove_integration_function($hook, $function);
