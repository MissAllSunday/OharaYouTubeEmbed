<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2016 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

class OHGifv implements iOharaYTEmbed
{
	protected $_app;
	public $siteSettings = array(
		'identifier' => 'gifv',
		'name' => 'Gifv',
		'code' => 'gifv',
		'js_inline' => '
	_ohSites.push({
		identifier: "gifv",
		noJS: true
	});
	',
		'js_file' => '',
		'css_file' => '',
		'regex' => '~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?i\.imgur\.com\/([a-z0-9]+)\.gifv(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | <\/a>  ))[?=&+%\w.-]*[\/\w\-_\~%@\?;=#}\\\\]?~ix',
		'before' => '[gifv]',
		'after' => '[/gifv]',
		'image' => 'gifv',
		'allowed_tag' => '',
	);

	/**
	 * An array holding all available tests for this particular site.
	 * The expected key is the, well, the expected result :P, the optimal and normal return value of {@link create()}
	 * The original key is itself an array containing all links to test against.
	 * Al tests uses the default width and height values: 480, 270.
	 * @access public
	 * @var array
	 */
	public $siteTests = array(
		'expected' => '<div class="oharaEmbed gifv" data-ohara_gifv="%7B%22video_id%22%3A%22joGlU0z%22%2C%22title%22%3A%22%22%7D" id="oh_gifv_joGlU0z" style="width: 480px; height: 270px;"><video preload="auto" autoplay="autoplay" loop="loop" style="max-width: 480px; max-height: 270px;" src="//i.imgur.com/joGlU0z.webm"><source src="//i.imgur.com/joGlU0z.webm" type="video/webm"></source></video></div>',
		'original' => array(
			'http://i.imgur.com/joGlU0z.gifv',
		),
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
		$pattern = '/^(?:https?:\/\/)?(?:www\.)?i\.imgur\.com\/([a-z0-9]+)\.gifv/i';

		// First attempt, pure regex.
		if (empty($result) && preg_match($pattern, $data, $matches))
			$result = isset($matches[1]) ? $matches[1] : false;

		// Got something, lets attempt to retreive the video title and some other info too.
		if (!empty($result))
		{
			// Default values.
			$params = array(
				'video_id' => $result,
				'title' => '',
			);

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
				if (!empty($matches) && !empty($matches[0]))
					return $that->content($matches[0]);

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

		return !empty($params) ? '<div class="oharaEmbed '. $this->siteSettings['identifier'] .'" data-ohara_'. $this->siteSettings['identifier'] .'="'. urlencode($paramsJson) .'" id="oh_'. $this->siteSettings['identifier'] .'_'. $params['video_id'] .'" style="width: '. $this->_app->width .'px; height: '. $this->_app->height .'px;"><video preload="auto" autoplay="autoplay" loop="loop" style="max-width: '. $this->_app->width .'px; max-height: '. $this->_app->height .'px;" src="//i.imgur.com/'. $params['video_id'] .'.webm"><source src="//i.imgur.com/'. $params['video_id'] .'.webm" type="video/webm"></source></video></div>' : '';
	}

	public function invalid()
	{
		return $this->_app->parser($this->_app->text('invalid_link'), array('site' => $this->siteSettings['name']));
	}
}
