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

class OHVimeo implements iOharaYTEmbed
{
	public $siteSettings = array(
		'identifier' => 'vimeo',
		'name' => 'Vimeo',
		'code' => 'vimeo',
		'js_inline' => '',
		'js_file' => '',
		'css_file' => '',
		'regex' => '~(?<=[\s>\.(;\'"]|^)(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix',
		'before' => '[vimeo]',
		'after' => '[/vimeo]',
		'image' => 'vimeo',
		'allowed_tag' => '',
	);

	public function __construct($app)
	{
		$this->_app = $app;
	}

	public function content($data)
	{
		// Use vimeo's API.

		// Need a function in a far far away file...
		require_once($this->_app->sourceDir .'/Subs-Package.php');

		// Construct the URL
		$oembed = '//vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. ($this->_app->width) .'&height='. ($this->_app->height);

		// Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
		$jsonArray = json_decode(fetch_web_data($oembed), true);

		if (!empty($jsonArray) && is_array($jsonArray))
		{
			// We use camelCase ;)
			$jsonArray['videoId'] = $jsonArray['video_id'];

			// The API returns too much stuff! easier to just whitelist what we want ;)
			$whiteList = array('title', 'videoId', 'thumbnail_url');
			$filtered = array_intersect_key($jsonArray, array_flip($whiteList));

			return $this->create($filtered);
		}

		else
			return $this->invalid();

		// If we reach this place, it means everything else failed miserably...
		return $data;
	}

	public function auto(&$message)
	{
		// Need something to work with.
		if (empty($message))
			return;

		// Quick fix for PHP lower than 5.4.
		$that = $this;

		$message = preg_replace_callback(
			$this->siteSettings['regex'],
			function ($matches) use($that)
			{
				if (!empty($matches) && !empty($matches[1]))
					return $that->create($matches[1]);

				else
					return $that->invalid();
			},
			$message
		);

		// Get lost!
		unset($that);
	}

	public function create($videoID)
	{
		return !empty($videoID) ? '<div class="oharaEmbed"><iframe width="'. $this->_app->width .'" height="'. $this->_app->height .'" src="//player.vimeo.com/video/'. $videoID .'" frameborder="0"></iframe></div>' : '';
	}

	public function invalid()
	{
		return $this->_app->parser($this->_app->text('invalid_link'), array('site' => $this->siteSettings['name']));
	}
}
