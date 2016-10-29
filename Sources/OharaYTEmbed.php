<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 2.1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2016 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

// Ohara autoload!
require_once $sourcedir .'/ohara/src/Suki/autoload.php';
require_once ($sourcedir .'/iOharaYTEmbed.php');

use Suki\Ohara;

class OharaYTEmbed extends Suki\Ohara
{
	public $name = __CLASS__;
	public $width;
	public $height;
	public static $sites = array();

	protected $_useConfig = true;

	public function __construct()
	{
		// Get yourself noted.
		$this->setRegistry();

		// Get the default settings.
		$this->defaultSettings();

		// Get a list of all available sites.
		$this->getSites();

		// Set our css bits.
		$this->addCss();
	}

	public function defaultSettings()
	{
		$this->width = $this->setting('width', 480);
		$this->height = $this->setting('height', 270);

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
					require_once $this->sitesFolder .'/'. $file;
					static::$sites[$filename] = new $filename($this);
				}
			}

		return static::$sites;
	}

	//Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^.
	public function addSettings(&$config_vars)
	{
		$config_vars[] = $this->text('title');
		$config_vars[] = array('check', $this->name .'_enable', 'subtext' => $this->text('enable_sub'));
		$config_vars[] = array('check', $this->name .'_autoEmbed', 'subtext' => $this->text('autoEmbed_sub'));
		$config_vars[] = array('int', $this->name .'_width', 'subtext' => $this->text('width_sub'), 'size' => 3);
		$config_vars[] = array('int', $this->name .'_height', 'subtext' => $this->text('height_sub'), 'size' => 3);

		// Gotta include a setting for the sites. Make sure the txt string actually exists!
		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site))
				$config_vars[] = array('check', $this->name .'_enable_'. $site->siteSettings['identifier'], 'label' => $this['tools']->parser($this->text('enable_generic'), array('site' => $site->siteSettings['name'])));

		$config_vars[] = '';
	}

	public function addCode(&$codes)
	{
		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		// Quick fix for PHP below 5.4.
		$that = $this;

		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->enable('enable_'. $site->siteSettings['identifier']))
			{
				$codes[] = array(
					'tag' => $site->siteSettings['identifier'],
					'type' => 'unparsed_content',
					'content' => '$1',
					'validate' => function (&$tag, &$data, $disabled) use ($that, $site)
					{
						// This BBC is currently disabled.
						if (!empty($disabled[$site->siteSettings['identifier']]))
							return;

						$data = empty($data) ? $site->invalid() : $site->content(trim(strtr($data, array('<br />' => ''))));
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
							// This extra tag is currently disabled.
							if (!empty($disabled[$site->siteSettings['extra_tag']]))
								return;

							$data = empty($data) ? $site->invalid() : $site->content(trim(strtr($data, array('<br />' => ''))));
						},
						'disabled_content' => '$1',
						'block_level' => true,
					);
			}

		// No longer needed.
		unset($that);
	}

	//The bbc button.
	public function addButtons(&$dummy)
	{
		global $context;

		// Mod is disabled.
		if (!$this->enable('enable'))
			return;

		$buttons = array();

		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->enable('enable_'. $site->siteSettings['identifier']))
				$buttons[] = array(
					'code' => $site->siteSettings['identifier'],
					'description' => $this['tools']->parser($this->text('desc_generic'), array('site' => $site->siteSettings['name'])),
					'before' => $site->siteSettings['before'],
					'after' => $site->siteSettings['after'],
					'image' => $site->siteSettings['image'],
				);

		if (!empty($buttons) && is_array($buttons))
			$context['bbc_tags'][count($context['bbc_tags']) - 1] = array_merge($context['bbc_tags'][count($context['bbc_tags']) - 1], $buttons);
	}

	public function addEmbed(&$message, &$smileys, &$cache_id, &$parse_tags)
	{
		global $context;

		// Mod is disabled or the user don't want to use autoEmbed or somebody else do not want this to happen.
		if (!$this->enable('enable') || !$this->enable('autoEmbed') || !empty($context['ohara_disable']))
			return;

		// As always, the good old foreach saves the day!
		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->enable('enable_'. $site->siteSettings['identifier']))
				$site->auto($message);
	}

	public function addCss()
	{
		global $context;

		// The much needed css file.
		loadCSSFile('oharaEmbed.css', array('force_current' => false, 'validate' => true, 'minimize' => true));
		loadJavaScriptFile('ohvideos.min.js', array('local' => true, 'force_current' => false, 'defer' => true, 'minimize' => false));

		// Set a max width var to let the JS code know how to act and react!
		addInlineJavaScript('
	var _ohWidth = '. $this->width .';
	var _ohHeight = '. $this->height .';
	var _ohSites = [];');

		foreach (static::$sites as $site)
			if (!empty($site) && is_object($site) && $this->setting('enable_'. $site->siteSettings['identifier']))
			{
				// The js file is expected to be located at the default theme's script folder and needs to include its own extension!
				if (!empty($site->siteSettings['js_file']))
					loadJavaScriptFile($site->siteSettings['js_file'], array('local' => true, 'default_theme' => true, 'defer' => true, 'minimize' => true));

				// The css file is expected to be located at the default theme's script folder and needs to include its own extension!
				if (!empty($site->siteSettings['css_file']))
					loadCSSFile($site->siteSettings['css_file'], array('force_current' => false, 'validate' => true, 'minimize' => true));

				// Is there any inline or JS file to be loaded? Please be sure to add a new line at the beginning and end of your string and to follow proper indent style too!
				if (!empty($site->siteSettings['js_inline']))
					addInlineJavaScript($site->siteSettings['js_inline']);

				// Does this site wants to add their own unique HTML tag? SMF already supports div by default.
				if (!empty($site->siteSettings['allowed_tag']))
					$context['allowed_html_tags'][] = $site->siteSettings['allowed_tag'];
			}
	}
}

/* Slowly repeating
...Sunday morning */
