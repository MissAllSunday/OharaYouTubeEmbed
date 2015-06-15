<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2015 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

interface iOharaYTEmbed
{
	public function content($data);

	public function auto(&$message);

	public function create($videoID);

	public function invalid();
}