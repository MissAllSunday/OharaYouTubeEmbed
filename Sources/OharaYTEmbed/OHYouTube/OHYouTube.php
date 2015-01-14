<?php

if (!defined('SMF'))
	die('No direct access...');

class OHYouTube extends OharaYTEmbed
{
	public $siteSettings = array(
		'identifier' => 'youtube',
		'name' => 'You Tube',
		'image' => 'youtube.png',
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

		// Does this particular site is enabled?
		if (!$this->setting('_'. $this->siteSettings['name'] .'_enable'))
			return str_replace('{site}', $this->siteSettings['name'], $this->text('disabled_generic'));

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
			return str_replace('{site}', $this->siteSettings['name'], $that->text('invalid_link'));

		// Got something, return it!.
		return '<div class="youtube" id="'. $result .'" style="width: '. $this->width .'px; height: '. $this->height .'px;"></div>';
	}
}
