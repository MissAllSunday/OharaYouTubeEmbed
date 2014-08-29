<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

if (version_compare(PHP_VERSION, '5.3.0', '<'))
	exit('This mod needs PHP 5.3 or greater. You will not be able to install/use this mod, contact your host and ask for a php upgrade.');

$hooks = array(
	'integrate_bbc_codes' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::code#',
	'integrate_bbc_buttons' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::button#',
	'integrate_general_mod_settings' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::settings#',
	'integrate_credits' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::who#',
	'integrate_pre_parsebbc' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::autoEmbed#',
	'integrate_load_theme' => '$sourcedir/OharaYTEmbed.php|OharaYTEmbed::css#',
);

foreach ($hooks as $hook => $function)
	add_integration_function($hook, $function);
