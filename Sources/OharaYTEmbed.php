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

// Use Ohara! manually :(
require_once ($sourcedir .'/ohara/src/Suki/Ohara.php');

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

		// Get the default settings.
		$this->defaultSettings();

		// Get a list of all available sites.
		$this->getSites();
	}

	public function defaultSettings()
	{
		$this->width = $this->enable('width') ? $this->setting('width') : '420';
		$this->height = $this->enable('height') ? $this->setting('height') : '315';

		$this->sitesFolder = $this->sourceDir . '/'. $this->name;
	}

	public function getSites()
	{
		$directories = array_diff(scandir($this->sitesFolder), array('..', '.'));

		if (empty(static::$sites) && !empty($directories) && is_array($directories))
			foreach ($directories as $file)
			{
				// Does it exists?
				if (file_exists($this->sitesFolder .'/'. $file))
				{
					$filename = pathinfo($this->sitesFolder .'/'. $file, PATHINFO_FILENAME);
					include_once $this->sitesFolder .'/'. $file;
					static::$sites[$filename] = new $filename;
				}
			}

		return static::$sites;
	}

	//Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^.
	public function settings(&$config_vars)
	{
		global $txt;

		$config_vars[] = $this->text('title');
		$config_vars[] = array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub'));
		$config_vars[] = array('check', $this->name .'_autoEmbed', 'subtext' => $this->text('autoEmbed_sub'));
		$config_vars[] = array('int', $this->name .'_width', 'subtext' => $this->text('width_sub'), 'size' => 3);
		$config_vars[] = array('int', $this->name .'_height', 'subtext' => $this->text('height_sub'), 'size' => 3);

		// Gotta include a setting for the sites. Make sure the txt string actually exists!
		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site))
				$config_vars[] = array('check', $this->name .'_enable_'. $site->siteSettings['identifier'], 'label' => str_replace('{site}', $site->siteSettings['name'], $this->text('enable_generic')));

		$config_vars[] = '';
	}

	public function code(&$codes)
	{
		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		// Quick fix for PHP below 5.4.
		$that = $this;

		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->setting('enable_'. $site->siteSettings['identifier']))
			{
				$codes[] = array(
					'tag' => $site->siteSettings['identifier'],
					'type' => 'unparsed_content',
					'content' => '$1',
					'validate' => function (&$tag, &$data, $disabled) use ($that, $site)
					{
						$data = empty($data) ? str_replace('{site}', $site->siteSettings['name'], $that->text('unvalid_link')) : $site->content(trim(strtr($data, array('<br />' => ''))));
					},
					'disabled_content' => '$1',
					'block_level' => true,
				);

				// Any extra tags?
				if (!empty($site->siteSettings['extra_tag']))
					$codes[] = array(
						'tag' => $site->siteSettings['extra_tag'],
						'type' => 'unparsed_content',
						'content' => '$1',
						'validate' => function (&$tag, &$data, $disabled) use ($that, $site)
						{
							$data = empty($data) ? str_replace('{site}', $site->siteSettings['name'], $that->text('unvalid_link')) : $site->content(trim(strtr($data, array('<br />' => ''))));
						},
						'disabled_content' => '$1',
						'block_level' => true,
					);
			}

		// No longer needed.
		unset($that);
	}

	//The bbc button.
	public function button(&$dummy)
	{
		global $context;

		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		$buttons = array();

		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->setting('enable_'. $site->siteSettings['identifier']))
				$buttons[] = array(
					'code' => $site->siteSettings['identifier'],
					'description' => str_replace('{site}', $site->siteSettings['name'], $this->text('desc_generic')),
					'before' => $site->siteSettings['before'],
					'after' => $site->siteSettings['after'],
					'image' => $site->siteSettings['image'],
				);

		if (!empty($buttons) && is_array($buttons))
			$context['bbc_tags'][count($context['bbc_tags']) - 1] = array_merge($context['bbc_tags'][count($context['bbc_tags']) - 1], $buttons);
	}

	public function autoEmbed(&$message, &$smileys, &$cache_id, &$parse_tags)
	{
		// Mod is disabled or the user don't want to use autoEmbed.
		if (!$this->enable('enable') || !$this->enable('autoEmbed'))
			return;

		// As always, the good old foreach saves the day!
		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->setting('enable_'. $site->siteSettings['identifier']))
				$site->auto($message);
	}

	public function css()
	{
		global $context;

		// The much needed css file.
		loadCSSFile('oharaEmbed.css', array('force_current' => false, 'validate' => true));

		// Add the iframe to the list of allowed tags.
		$context['allowed_html_tags'][] = '<iframe>';

		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->setting('enable_'. $site->siteSettings['identifier']))
			{
				// The js file is expected to be located at the default theme's script folder and needs to include its own extension!
				if (!empty($site->siteSettings['js_file']))
					loadJavascriptFile($site->siteSettings['js_file'], array('local' => true, 'default_theme' => true, 'defer' => true));

				// The css file is expected to be located at the default theme's script folder and needs to include its own extension!
				if (!empty($site->siteSettings['css_file']))
					loadCSSFile($site->siteSettings['css_file'], array('force_current' => false, 'validate' => true));

				// Is there any inline or JS file to be loaded? Please be sure to add a new line at the beginning and end of your string and to follow proper indent style too!
				if (!empty($site->siteSettings['js_inline']))
					addInlineJavascript($site->siteSettings['js_inline'], true);

				// Do this site wants to add their own unique tag? SMF already supports div and the mod adds iframe by default.
				if (!empty($site->siteSettings['allowed_tag']))
					$context['allowed_html_tags'][] = $site->siteSettings['allowed_tag'];
			}
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
