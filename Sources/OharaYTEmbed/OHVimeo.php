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
		'js_inline' => '
	_ohSites.push({
		identifier: "vimeo",
		baseUrl: "//player.vimeo.com/video/{video_id}",
		getImage: "getVimeoImage"
	});
	',
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
		// If the ID was provided, turn it into a generic url.
		if (is_numeric($data)
			$data = '//vimeo.com/'. $data;

		// Need a function in a far far away file...
		require_once($this->_app->sourceDir .'/Subs-Package.php');

		// Construct the URL
		$oembed = '//vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. ($this->_app->width) .'&height='. ($this->_app->height);

		// Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
		$jsonArray = json_decode(fetch_web_data($oembed), true);

		if (!empty($jsonArray) && is_array($jsonArray))
		{
			// The API returns too much stuff! easier to just whitelist what we want ;)
			$whiteList = array('title', 'video_id', 'thumbnail_url');
			$filtered = array_intersect_key($jsonArray, array_flip($whiteList));

			// Get a more generic name.
			$filtered['imageUrl'] = $filtered['thumbnail_url'];
			unset($filtered['thumbnail_url']);

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
					return $that->content($matches[1]);

				else
					return $that->invalid();
			},
			$message
		);

		// Get lost!
		unset($that);
	}

	public function create($params = array())
	{
		// Make sure not to use any unvalid params.
		$paramsJson = !empty($params) ? json_encode($params) : '{}';

		return !empty($params) ? '<div class="oharaEmbed vimeo" data-ohara_'. $this->siteSettings['identifier'] .'="'. $paramsJson .'" id="oh_'. $this->siteSettings['identifier'] .'_'. $params['videoID'] .'" style="width: '. $this->_app->width .'px; height: '. $this->_app->height .'px;"></div>' : '';
	}

	public function invalid()
	{
		return $this->_app->parser($this->_app->text('invalid_link'), array('site' => $this->siteSettings['name']));
	}
}
