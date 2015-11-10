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

class OHYouTube implements iOharaYTEmbed
{
	protected $_app;
	public $siteSettings = array(
		'identifier' => 'youtube',
		'name' => 'You Tube',
		'code' => 'youtube',
		'extra_tag' => 'yt',
		'js_inline' => '
	_ohSites.push({
		identifier: "youtube",
		baseUrl: "//www.youtube.com/watch?v={video_id}",
		getImage: "getYoutubeImage"
	});
	',
		'js_file' => 'ohyoutube.min.js',
		'css_file' => '',
		'regex' => '~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix',
		'before' => '[youtube]',
		'after' => '[/youtube]',
		'image' => 'youtube',
		'allowed_tag' => '',
	);

	public function __construct($app)
	{
		$this->_app = $app;
	}

	// The main class already checks for empty and calls invalid() so no need to check for those things again
	public function content($data)
	{
		//Set a local var for laziness.
		$result = '';

		// We all love Regex.
		$pattern = '#(?:https?:\/\/)?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?:.+)?$#x';

		// Check if the user provided the youtube ID
		if (preg_match('/^[a-zA-z0-9_-]{11}$/', $data) > 0)
			$result = $data;

		// First attempt, pure regex.
		elseif (empty($result) && preg_match($pattern, $data, $matches))
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

		// Got something, lets attempt to retreive the video title and some other info too.
		if (!empty($result)
		{
			// Need a function in a far far away file...
			require_once($this->_app->sourceDir .'/Subs-Package.php');

			// Default values.
			$params = array(
				'video_id' => $result,
				'title' => '',
			);

			// Construct the URL
			$oembed = '//youtube.com/get_video_info?video_id=' . $result;
			$content = '';
			$cResult = array();
			$content = fetch_web_data($oembed);
			parse_str($content, $cResult);

			// Some videos has weird restrictions.
			if (!empty($cResult)
				if (!empty($cResult['status'] && $cResult['status'] == 'ok')
				{
					$params['title'] = $cResult['title'];
					$params['imageUrl'] = (!empty($cResult['iurl'] ? $cResult['iurl'] : $cResult['iurlsd']);
				}

			return $this->create($params);
		}

		// At this point, all tests had miserably failed or we got something.
		else
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
