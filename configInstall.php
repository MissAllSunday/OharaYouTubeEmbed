<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2016 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

else if(!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php and SSI.php files.');

if ((SMF == 'SSI') && !$user_info['is_admin'])
	die('Admin priveleges required.');

// Prepare and insert this mod's config array.
$_config = array(
	'_availableHooks' => array(
		'code' => 'integrate_bbc_codes',
		'buttons' => 'integrate_bbc_buttons',
		'settings' => 'integrate_general_mod_settings',
		'embed' => 'integrate_pre_parsebbc'
	),
);

// All good.
updateSettings(array('_configBlogNews' => json_encode($_config)));

if (SMF == 'SSI')
	echo 'Database changes are complete!';