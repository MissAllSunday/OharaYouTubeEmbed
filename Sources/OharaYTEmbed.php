<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2011, 2012, 2013, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function OYTE_bbc_add_code(&$codes)
{
	global $modSettings;

	if (empty($modSettings['OYTE_master']))
		return;

	array_push($codes,
		array(
			'tag' => 'youtube',
			'type' => 'unparsed_content',
			'content' => '<div style="text-align:center;margin:auto;padding:5px;" class="youtube $1">
				$1
			</div>',
			'validate' => function (&$tag, &$data, $disabled)
			{
				global $txt;

				loadLanguage('OharaYTEmbed');

				if (empty($data))
					$data = sprintf($txt['OYTE_unvalid_link'], 'youtube');

				else
					$data = OYTE_Main(trim(strtr($data, array('<br />' => ''))));

			},
			'disabled_content' => '$1',
			'block_level' => true,
		),
		array(
			'tag' => 'yt',
			'type' => 'unparsed_content',
			'content' => '<div style="text-align:center;margin:auto;padding:5px;" class="youtube $1">
				$1
			</div>',
			'validate' => function (&$tag, &$data, $disabled)
			{
				global $txt;

				loadLanguage('OharaYTEmbed');

				if (empty($data))
					$data = sprintf($txt['OYTE_unvalid_link'], 'youtube');

				else
					$data = OYTE_Main(trim(strtr($data, array('<br />' => ''))));

			},
			'disabled_content' => '$1',
			'block_level' => true,
		),
		array(
			'tag' => 'vimeo',
			'type' => 'unparsed_content',
			'content' => '<div style="text-align:center;margin:auto;padding:5px;">
				$1
			</div>',
			'validate' => function (&$tag, &$data, $disabled)
			{
				global $txt;

				loadLanguage('OharaYTEmbed');

				if (empty($data))
					$data = sprintf($txt['OYTE_unvalid_link'], 'vimeo');

				else
					$data = OYTE_Vimeo(trim(strtr($data, array('<br />' => ''))));

			},
			'disabled_content' => '$1',
			'block_level' => true,
		)
	);
}

 // The bbc button.
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

	$buttons[count($buttons) - 1][] =array(
		'image' => 'vimeo',
		'code' => 'vimeo',
		'before' => '[vimeo]',
		'after' => '[/vimeo]',
		'description' => $txt['OYTE_vimeo_desc'],
	);

}

// Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^.
function OYTE_settings(&$config_vars)
{
	global $txt;

	loadLanguage('OharaYTEmbed');

	$config_vars[] = $txt['OYTE_title'];
	$config_vars[] = array('check', 'OYTE_master', 'subtext' => $txt['OYTE_master_sub']);
	$config_vars[] = array('check', 'OYTE_autoEmbed', 'subtext' => $txt['OYTE_autoEmbed_sub']);
	$config_vars[] = array('int', 'OYTE_video_width', 'subtext' => $txt['OYTE_video_width_sub'], 'size' => 3);
	$config_vars[] = array('int', 'OYTE_video_height', 'subtext' => $txt['OYTE_video_height_sub'], 'size' => 3);
	$config_vars[] = '';
}

// Take the url, take the video ID and return the embed code.
function OYTE_Main($data)
{
	global $modSettings, $txt;

	loadLanguage('OharaYTEmbed');

	if (empty($data))
		return sprintf($txt['OYTE_unvalid_link'], 'youtube');

	// Set a local var for laziness.
	$result = '';

	 // We all love Regex.
	$pattern = '#^(?:https?://)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';

	// First attempt, pure regex.
	if (preg_match($pattern, $data, $matches))
		$result = isset($matches[1]) ? $matches[1] : false;

	// Give another regex a chance.
	elseif (empty($result) && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $data, $match))
		$result = isset($match[1]) ? $match[1] : false;

	// No?, then one last chance, let PHPs native parse_url() function do the dirty work./
	elseif (empty($result))
	{
		// This relies on the url having ? and =, this is only an emergency check.
		parse_str(parse_url($data, PHP_URL_QUERY), $result);
		$result = isset($result['v']) ? $result['v'] : false;
	}

	// Build the iframe.
	if (!empty($result))
		return '<iframe width="'. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'" height="'. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']) .'" src="http://www.youtube.com/embed/'. $result .'" frameborder="0"></iframe>';

	// At this point, all tests had miserably failed.
	else
		return sprintf($txt['OYTE_unvalid_link'], 'youtube');
}

function OYTE_Vimeo($data)
{
	global $modSettings, $txt, $sourcedir;

	if (empty($data))
		return sprintf($txt['OYTE_unvalid_link'], 'vimeo');

	loadLanguage('OharaYTEmbed');

	// First try, pure regex.
	$r = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/';

	// Get the video ID.
	if (preg_match($r, $data, $matches))
		$videoID = isset($matches[1]) ? $matches[1] : false;

	if (!empty($videoID) && ctype_digit($videoID))
	{
		// Build the iframe.
		return '<iframe src="//player.vimeo.com/video/'. $videoID .'" width="'. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'" height="'. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']) .'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}

	// Nope? then fall back to vimeo's API.
	else
	{
		// Need a function in a far far away file...
		require_once($sourcedir .'/Subs-Package.php');

		// Construct the URL
		$oembed = 'http://vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'&height='. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']);

		//Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
		$jsonArray = json_decode(fetch_web_data($oembed), true);

		if (!empty($jsonArray) && is_array($jsonArray) && !empty($jsonArray['html']))
			return $jsonArray['html'];

		else
			return sprintf($txt['OYTE_unvalid_link'], 'vimeo');
	}

	// If we reach this place, it means everything else failed miserably...
	return sprintf($txt['OYTE_unvalid_link'], 'vimeo');
}

function OYTE_Preparse($message)
{
	global $modSettings;

	// The mod is disabled or the admin doesn't want to auto-embed videos.
	if (empty($modSettings['OYTE_master']) || empty($modSettings['OYTE_autoEmbed']))
		return $message;

	// The extremely long regex...
	$vimeo = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/';
	$youtube = '~(?<=[\s>\.(;\'"]|^)(?:https?:\/\/)?(?:[0-9A-Z-]+\.)?(?:youtu\.be/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix';

	if (empty($message))
		return false;

	// Is this a YouTube video url?
	$message = preg_replace_callback(
		$youtube,
		function ($matches) {
			return '[youtube]'. $matches[0] .'[/youtube]';
		},
		$message
	);

	// A Vimeo url perhaps?
	$message = preg_replace_callback(
		$vimeo,
		function ($matches) {
			return '[vimeo]'. $matches[0] .'[/vimeo]';
		},
		$message
	);

	return $message;
}

// DUH! WINNING!
function OYTE_care(&$dummy)
{
	global $context;

	if (isset($context['current_action']) && $context['current_action'] == 'credits')
		$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" target="_blank" title="Free SMF mods">Ohara YouTube Embed mod &copy Suki</a>';
}

	// Slowly repeating
	// ...Sunday morning.
