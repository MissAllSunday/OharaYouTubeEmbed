<?php

/**
 * @package Ohara Youtube Embed mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	function OYTE_bbc_add_code(&$codes)
	{
		global $modSettings;

		if (empty($modSettings['OYTE_master']))
			return;

		$codes[] = array(
			'tag' => 'youtube',
			'type' => 'unparsed_content',
			'content' => '$1',
			'validate' => create_function('&$tag, &$data, $disabled', '
				global $txt;

				if (empty($data))
					$data = $txt[\'OYTE_unvalid_link\'];

				else
				{
					$data = trim(strtr($data, array(\'<br />\' => \'\')));
					$data = OYTE_Main($data);
				}
			'),
			'disabled_content' => '$1',
			'block_level' => true,
			'test' => '(http://(?:www\.)?youtu(?:be\.com/watch\?v=|\.be/)(\w*)(&(amp;)?[\w\?=]*)?)'
		);
	}

	 /* The bbc button */
	function OYTE_bbc_add_button($buttons)
	{
		global $txt, $modSettings;

		loadLanguage('OharaYTEmbed');

		if (empty($modSettings['OYTE_master']))
			return;

			$buttons[count($buttons) - 1][] = array(
				'image' => 'youtube',
				'code' => 'youtube',
				'before' => '[youtube]',
				'after' => '[/youtube]',
				'description' => $txt['OYTE_desc'],
			);
	}

	/* Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^ */
	function OYTE_settings(&$config_vars)
	{
		global $txt;

		loadLanguage('OharaYTEmbed');

		$config_vars[] = $txt['OYTE_title'];
		$config_vars[] = array('check', 'OYTE_master', 'subtext' => $txt['OYTE_master_sub']);
		$config_vars[] = array('int', 'OYTE_video_width', 'subtext' => $txt['OYTE_video_width_sub'], 'size' => 3);
		$config_vars[] = array('int', 'OYTE_video_height', 'subtext' => $txt['OYTE_video_height_sub'], 'size' => 3);
		$config_vars[] = '';
	}

	/* Take the url, take the video ID and return the embed code */
	function OYTE_Main($data)
	{
		global $modSettings, $txt;

		loadLanguage('OharaYTEmbed');

		 /* Regex time */
		$pattern = '((http|https)://(?:www\.)?youtu(?:be\.com/watch\?v=|\.be/)(\w*)(&(amp;)?[\w\?=]*)?)';

		/* Is this a valid youtube url? */
		if (preg_match($pattern, $data))
		{
			$data = preg_replace($pattern, '$1', $data);
			$data = OYTE_Replace($data);

			return $data;
		}

		/* No? then return the unvalid link along with a text string */
		else
			return sprintf($txt['OYTE_unvalid_link'], $data);
	}

	/* A simple function to show the video with some parameters */
	function OYTE_Replace($string)
	{
		global $modSettings;

		/* So, the user did not set the width and height, use the default values then */
		$width = empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width'];
		$height = empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height'];

		/* Return the HTML  */
		$return = '
			<div style="text-align:center;margin:auto;padding:5px;" class="youtube '.$string.'">
				<iframe width="'.$width.'" height="'.$height.'" src="http://www.youtube.com/embed/'.$string.'" frameborder="0"></iframe>
			</div>';

		return $return;
	}

	/* DUH! WINNING! */
	function OYTE_Who()
	{
		$MAS = '<a href="http://missallsunday.com" title="Free SMF Mods">Ohara YouTube Embed mod &copy Suki</a>';

		return $MAS;
	}

	/* Slowly repeating
	...Sunday morning */
