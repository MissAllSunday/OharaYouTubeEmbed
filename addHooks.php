<?php

/**
 * @package Ohara Youtube Embed mod
 * @version 1.1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2011, 2012, 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2011, 2012, 2013,
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
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
	add_integration_function($hook, $function);
