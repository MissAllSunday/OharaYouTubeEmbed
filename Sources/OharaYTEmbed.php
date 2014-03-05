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
		'content' => '<div style="text-align:center;margin:auto;padding:5px;" class="youtube $1">
			<iframe width="'. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'" height="'. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']) .'" src="http://www.youtube.com/embed/$1" frameborder="0"></iframe>
		</div>',
		'validate' => create_function('&$tag, &$data, $disabled', '
			global $txt;

			if (empty($data))
				$data = $txt[\'OYTE_unvalid_link\'];

			else
				$data = OYTE_Main(trim(strtr($data, array(\'<br />\' => \'\'))));

		'),
		'disabled_content' => '$1',
		'block_level' => true,
	);
}

 /* The bbc button */
function OYTE_bbc_add_button(&$buttons)
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

	/* Set a local var for laziness */
	$result = '';

	 /* We all love Regex */
	$pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';

	/* First attempt, pure regex */
	if (preg_match($pattern, $data, $matches))
		$result = isset($matches[1]) ? $matches[1] : false;

	/* Give another regex a chance */
	elseif (empty($result) && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $data, $match))
		$result = isset($match[1]) ? $match[1] : false;

	/* No?, then one last chance, let PHPs native parse_url() function do the dirty work */
	elseif (empty($result))
	{
		/* This relies on the url having ? and =, this is only an emergency check */
		parse_str(parse_url($data, PHP_URL_QUERY), $result);
		$result = isset($result['v']) ? $result['v'] : false;
	}

	/* At this point, all tests had miserably failed */
	if (empty($result))
		return sprintf($txt['OYTE_unvalid_link'], $data);

	return $result;
}

function OYTE_Preparse(&$message)
{
	$vimeo = '/(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)?(?:www\.)?vimeo.com\/(?:album\/|groups\/|channels\/)?[0-9]+\??[/\w\-_\~%@\?;=#}\\\\]?/';
	$youtube = '~(?<=[\s>\.(;\'"]|^)https?://(?:[0-9A-Z-]+\.)?(?:youtu\.be/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix';

	if (empty($message))
		return false;

	// Is this a YouTube video url?
	if ($y = preg_replace_callback(
		$youtube,
		function ($matches) {
			return '[youtube]'. $matches[0] .'[/youtube]';
		},
		$v
	);)
		$message = $v;

	// A Vimeo url perhaps?
	if ($v = preg_replace_callback(
		$vimeo,
		function ($matches) {
			return '[vimeo]'. $matches[0] .'[/vimeo]';
		},
		$v
	);)
		$message = $y;
}

/* DUH! WINNING! */
function OYTE_care(&$dummy)
{
	global $context;

	if (isset($context['current_action']) && $context['current_action'] == 'credits')
		$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">Ohara YouTube Embed mod &copy Suki</a>';
}

	/* Slowly repeating
	...Sunday morning */
