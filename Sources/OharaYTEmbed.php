<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (C) 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('Hacking attempt...');

// Use composer!
require_once ($boarddir .'/vendor/suki/ohara/src/Suki/Ohara.php');

class OharaYTEmbed extends Suki\Ohara
{
	public $name = __CLASS__;
	public $width;
	public $height;
	public static $sites = array();

	public function __construct()
	{
		// Get yourself noted.
		$this->setRegistry();


		$this->width = $this->enable('width') ? $this->setting('width') : '420';
		$this->height = $this->enable('height') ? $this->setting('height') : '315';

		$this->sitesFolder = $this->sourceDir . '/'. $this->name;

		// Get a list of all available sites.
		$this->getSites();
	}

	public function getSites()
	{
		$directories = glob($this->sitesFolder . '/*' , GLOB_ONLYDIR);

		if (empty(self::$sites))
			foreach ($directories as $dir)
			{
				$name = basename($dir);

				// Does it exists?
				if (file_exists($this->sitesFolder .'/'. $name .'/'. $name .'.php'))
					self::$sites[] = include_once $this->sitesFolder .'/'. $name .'/'. $name .'.php';
			}

		return self::$sites;
	}

	//Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^.
	public function settings(&$config_vars)
	{
		$config_vars[] = $this->text('title');
		$config_vars[] = array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub'));
		$config_vars[] = array('check', $this->name .'_autoEmbed', 'subtext' => $this->text('autoEmbed_sub'));
		$config_vars[] = array('int', $this->name .'_width', 'subtext' => $this->text('width_sub'), 'size' => 3);
		$config_vars[] = array('int', $this->name .'_height', 'subtext' => $this->text('height_sub'), 'size' => 3);

		// Gotta include a setting for the sites.
		foreach (self::$sites as $site)
			$config_vars[] = array('check', $this->name .'_'. $site['name'] .'_enable',);

		$config_vars[] = '';
	}

	public function code(&$codes)
	{
		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		foreach (self::$sites as $site)
			if (!empty($site) && !empty($site['fucntion']))
			{
				$codes[] = array(
					'tag' => $site['tag'],
					'type' => 'unparsed_content',
					'content' => '$1',
					'validate' => function (&$tag, &$data, $disabled) use ($that, $site)
					{
						$data = empty($data) ? sprintf($that->text('unvalid_link'), $site['name']) : $site['fucntion'](trim(strtr($data, array('<br />' => ''))));

						// Can't do this check on the plugins function so...
						if ($data == 'invalid')
							$data = sprintf($that->text('invalid_link'), $site['name']);
					},
					'disabled_content' => '$1',
					'block_level' => true,
				);

				// Any extra tags?
				if (!empty($site['extra_tag']))
					$codes[] = array(
						'tag' => $site['extra_tag'],
						'type' => 'unparsed_content',
						'content' => '$1',
						'validate' => function (&$tag, &$data, $disabled) use ($that, $site)
						{
							$data = empty($data) ? sprintf($that->text('unvalid_link'), $site['name']) : $site['fucntion'](trim(strtr($data, array('<br />' => ''))));

							// Can't do this check on the plugins function so...
							if ($data == 'invalid')
								$data = sprintf($that->text('invalid_link'), $site['name']);
						},
						'disabled_content' => '$1',
						'block_level' => true,
					);
			}

		// No longer needed.
		unset($that);
	}

	//The bbc button.
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

	public function vimeo($data)
	{
		global $sourcedir;

		if (empty($data))
			return sprintf($this->text('unvalid_link'), 'vimeo');

		// To avoid all kinds of weirdness.
		$call = $this->create;

		// First try, pure regex.
		$r = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/';

		// Get the video ID.
		if (preg_match($r, $data, $matches))
			$videoID = isset($matches[1]) ? $matches[1] : false;

		if (!empty($videoID) && ctype_digit($videoID))
		{
			// Build the iframe.
			return $call($videoID, 'vimeo');
		}

		// Nope? then fall back to vimeo's API.
		else
		{
			// Need a function in a far far away file...
			require_once($sourcedir .'/Subs-Package.php');

			// Construct the URL
			$oembed = '//vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. ($this->width) .'&height='. ($this->height);

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

	public function autoEmbed(&$message, &$smileys, &$cache_id, &$parse_tags)
	{
		// Mod is disabled or the user don't want to use autoEmbed.
		if (!$this->enable('enable') || !$this->enable('autoEmbed'))
			return;

		// The extremely long regex...
		$vimeo = '~(?<=[\s>\.(;\'"]|^)(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix';
		$youtube = '~(?<=[\s>\.(;\'"]|^)(?:https?:\/\/)?(?:[0-9A-Z-]+\.)?(?:youtu\.be/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix';

		if (empty($message))
			return $message;

		// To avoid all kinds of weirdness.
		$call = $this->create;

		// Is this a YouTube video url?
		$message = preg_replace_callback(
			$youtube,
			function ($matches) use($call) {
				if (!empty($matches) && !empty($matches[1]))
					return $call($matches[1], 'youtube');

				else
					return sprintf($txt['OYTE_unvalid_link'], 'youtube');
			},
			$message
		);

		// A Vimeo url perhaps?
		$message = preg_replace_callback(
			$vimeo,
			function ($matches) use($call) {
				return $call($matches[1], 'vimeo');
			},
			$message
		);

		return $message;
	}

	public function css()
	{
		// The much needed css file.
		loadCSSFile('oharaEmbed.css', array('force_current' => false, 'validate' => true));
	}

	// DUH! WINNING!.
	public function who()
	{
		global $context;

		$context['copyrights']['mods'][] = '<a href="http://missallsunday.com" title="Free SMF Mods">Ohara Youtube and Vimeo (auto)embed mod &copy Suki</a>';
	}
}

/* Slowly repeating
...Sunday morning */
