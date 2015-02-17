<?php

if (!defined('SMF'))
	die('No direct access...');

class OHYouTube extends OharaYTEmbed
{
	public $siteSettings = array(
		'identifier' => 'youtube',
		'name' => 'You Tube',
		'code' => 'youtube',
		'extra_tag' => 'yt',
		'js_inline' => '',
		'js_file' => 'ohyoutube.js',
		'css_file' => '',
		'regex' => '~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?(?:youtu\.be/|youtube\.com(?:/embed/|/v/|/watch\?v=|/watch\?.+&v=))([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | </a>  ))[?=&+%\w.-]*[/\w\-_\~%@\?;=#}\\\\]?~ix',
		'before' => '[youtube]',
		'after' => '[/youtube]',
		'image' => 'youtube',
		'allowed_tag' => '',
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
			return $data;

		// Got something, return it!.
		return $this->create($result);
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
		return !empty($videoID) ? '<div class="oharaEmbed youtube" id="'. $videoID .'" style="width: '. $this->width .'px; height: '. $this->height .'px;"></div>' : '';
	}
}
