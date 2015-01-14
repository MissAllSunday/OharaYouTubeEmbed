<?php

if (!defined('SMF'))
	die('No direct access...');

global $txt;

$txt['OharaYTEmbed_youtube_enable'] = 'Enable youtube site';

return array(
	'name' => 'youtube',
	'image' => 'youtube',
	'tag' => 'youtbe',
	'extra_tag' => 'yt',
	'js_inline' => '',
	'js_file' => 'youtube.js',
	'function' => function ($data)
	{
		// Just return "invalid", the main class should know what to do.
		if (empty($data))
			return 'invalid';

		//Set a local var for laziness.
		$result = '';

		// We all love Regex.
		$pattern = '#(?:https?:\/\/)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';

		// First attempt, pure regex.
		if (preg_match($pattern, $data, $matches))
			$result = isset($matches[1]) ? $matches[1] : false;

		// Give another regex a chance.
		elseif (empty($result) && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $data, $match))
			$result = isset($match[1]) ? $match[1] : false;

		// No?, then one last chance, let PHPs native parse_url() function do the dirty work.
		elseif (empty($result))
		{
			// This relies on the url having ? and =, this is only an emergency check.
			parse_str(parse_url($data, PHP_URL_QUERY), $result);
			$result = isset($result['v']) ? $result['v'] : false;
		}

		// At this point, all tests had miserably failed.
		if (empty($result))
			return 'invalid';

		// Got something, return it!.
		return '<div class="youtube" id="'. $result .'" style="width: {width}px; height: {height}px;"></div>';
	},

);