<?php

if (!defined('SMF'))
	die('No direct access...');

class OHVimeo extends OharaYTEmbed
{
	public $siteSettings = array(
		'identifier' => 'vimeo',
		'name' => 'Vimeo',
		'code' => 'vimeo',
		'js_inline' => '',
		'js_file' => '',
		'regex' => '~(?<=[\s>\.(;\'"]|^)(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix',
		'before' => '[vimeo]',
		'after' => '[/vimeo]',
		'image' => 'vimeo',
	);

	public function __construct()
	{
		$this->setRegistry();

		// Get the default settings.
		$this->defaultSettings();
	}

	public function content($data)
	{
		// Return a nice "invalid" message.
		if (empty($data))
			return str_replace('{site}', $this->siteSettings['name'], $that->text('invalid_link'));

		// Does this particular site is enabled? No? then just return what was given to us...
		if (!$this->setting('enable_'. $this->siteSettings['identifier']))
			return $data;

		//Set a local var for laziness.
		$result = '';

		// First try, pure regex.
		$pattern = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/';

		// Get the video ID.
		if (preg_match($pattern, $data, $matches))
			$videoID = isset($matches[1]) ? $matches[1] : false;

		// Got anything?
		if (!empty($videoID) && ctype_digit($videoID))
			return $this->create($videoID);

		// Nope? then fall back to vimeo's API.
		else
		{
			// Need a function in a far far away file...
			require_once($this->sourceDir .'/Subs-Package.php');

			// Construct the URL
			$oembed = '//vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='. ($this->width) .'&height='. ($this->height);

			//Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
			$jsonArray = json_decode(fetch_web_data($oembed), true);

			if (!empty($jsonArray) && is_array($jsonArray) && !empty($jsonArray['html']))
				return $jsonArray['html'];

			else
				return str_replace('{site}', $this->siteSettings['name'], $that->text('invalid_link'));
		}

		// If we reach this place, it means everything else failed miserably...
		return str_replace('{site}', $this->siteSettings['name'], $that->text('invalid_link'));
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
			function ($matches) use($that) {
				if (!empty($matches) && !empty($matches[1]))
					return $that->create($matches[1]);

				else
					return str_replace('{site}', $that->siteSettings['name'], $that->text('invalid_link'));
			},
			$message
		);

		// Get lost!
		unset($that);
	}

	public function create($videoID)
	{
		return !empty($videoID) ? '<div class="oharaEmbed"><iframe width="'. $this->width .'" height="'. $this->height .'" src="//player.vimeo.com/video/'. $videoID .'" frameborder="0"></iframe></div>' : '';
	}
}
