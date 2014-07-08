<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2011, 2012, 2013, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('Hacking attempt...');

// There is no autoload feature on SMF so...
require_once($sourcedir . '/Ohara.php');

class OharaYTEmbed extends Ohara
{
	public static $name = __CLASS__;

	/* Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^ */
	public function settings(&$config_vars)
	{
		loadLanguage(self::$name);

		$config_vars[] = $this->text('title');
		$config_vars[] = array('check', '_enable', 'subtext' => $this->text('enable_sub'));
		$config_vars[] = array('check', '_autoEmbed', 'subtext' => $this->text('autoEmbed_sub'));
		$config_vars[] = array('int', '_width', 'subtext' => $this->text('width_sub'), 'size' => 3);
		$config_vars[] = array('int', '_height', 'subtext' => $this->text('height_sub'), 'size' => 3);
		$config_vars[] = '';
	}

	public function code(&$codes)
	{
		global $modSettings;

		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		// Quick fix for PHP lower than 5.4.
		$that = $this;

		array_push($codes,
			array(
				'tag' => 'youtube',
				'type' => 'unparsed_content',
				'content' => '<div style="text-align:center;margin:auto;padding:5px;" class="youtube $1">
					<iframe width="'. ($this->enable('width') ? $this->setting('width') : '420') .'" height="'. ($this->enable('height') ? $this->setting('height') : '315') .'" src="'. (!empty($modSettings['setting_secureCookies']) ? 'https' : 'http') .'://www.youtube.com/embed/$1" frameborder="0"></iframe>
				</div>',
				'validate' => function (&$tag, &$data, $disabled) use ($that)
				{
					$data = empty($data) ? $that->text('unvalid_link') : $that->youtube(trim(strtr($data, array('<br />' => ''))));
				},
				'disabled_content' => '$1',
				'block_level' => true,
			),
			array(
				'tag' => 'yt',
				'type' => 'unparsed_content',
				'content' => '<div style="text-align:center;margin:auto;padding:5px;" class="youtube $1">
					<iframe width="'. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'" height="'. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']) .'" src="'. (!empty($modSettings['setting_secureCookies']) ? 'https' : 'http') .'://www.youtube.com/embed/$1" frameborder="0"></iframe>
				</div>',
				'validate' => function (&$tag, &$data, $disabled) use ($that)
				{
					$data = empty($data) ? $that->text('unvalid_link') : $that->youtube(trim(strtr($data, array('<br />' => ''))));
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
				'validate' => function (&$tag, &$data, $disabled) use ($that)
				{
					$data = empty($data) ? $that->text('unvalid_link') : $that->vimeo(trim(strtr($data, array('<br />' => ''))));
				},
				'disabled_content' => '$1',
				'block_level' => true,
			)
		);
	}

	 /* The bbc button */
	public function button(&$buttons)
	{
		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		$buttons[count($buttons) - 1][] = array(
			'image' => 'youtube',
			'code' => 'youtube',
			'before' => '[youtube]',
			'after' => '[/youtube]',
			'description' => $this->text('desc'),
		);

		$buttons[count($buttons) - 1][] =array(
			'image' => 'vimeo',
			'code' => 'vimeo',
			'before' => '[vimeo]',
			'after' => '[/vimeo]',
			'description' => $this->text('vimeo_desc'),
		);

	}

	/* Take the url, take the video ID and return the embed code */
	public function youtube($data)
	{
		global $modSettings, $txt;

		loadLanguage('OharaYTEmbed');

		if (empty($data))
			return sprintf($txt['OYTE_unvalid_link'], 'youtube');

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
			return sprintf($txt['OYTE_unvalid_link'], 'youtube');

		return $result;
	}

	public function vimeo($data)
	{
		global $modSettings, $txt, $sourcedir;

		if (empty($data))
			return sprintf($txt['OYTE_unvalid_link'], 'vimeo');

		loadLanguage('OharaYTEmbed');

		// Need a function in a far far away file...
		require_once($sourcedir .'/Subs-Package.php');

		// Construct the URL
		$oembed = ''. (!empty($modSettings['setting_secureCookies']) ? 'https' : 'http') .'://vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. (empty($modSettings['OYTE_video_width']) ? '420' : $modSettings['OYTE_video_width']) .'&height='. (empty($modSettings['OYTE_video_height']) ? '315' : $modSettings['OYTE_video_height']);

		//Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
		$jsonArray = json_decode(fetch_web_data($oembed), true);

		if (!empty($jsonArray) && is_array($jsonArray) && !empty($jsonArray['html']))
			return $jsonArray['html'];

		else
			return sprintf($txt['OYTE_unvalid_link'], 'vimeo');
	}

	public function autoEmbed(&$message, &$smileys, &$cache_id, &$parse_tags)
	{
		// Mod is disabled or the user don't want to use autoEmbed.
		if (!$this->enable('enable') || !$this->enable('autoEmbed'))
			return;

		// The extremely long regex...
		$vimeo = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)?(?:www\.)?vimeo.com\/(?:album\/|groups\/(.*?)\/|channels\/(.*?)\/)?[0-9]+\??[/\w\-_\~%@\?;=#}\\\\]?~';
		$youtube = '~(?<=[\s>\.(;\'"]|^)https?://(?:[0-9A-Z-]+\.)?(?:youtu\.be/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix';

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

	/* DUH! WINNING! */
	public function who(&$dummy)
	{
		global $context;

		if (isset($context['current_action']) && $context['current_action'] === 'credits')
			$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" title="Free SMF Mods">Activity Bar mod &copy Suki</a>';
	}
}

	/* Slowly repeating
	...Sunday morning */
