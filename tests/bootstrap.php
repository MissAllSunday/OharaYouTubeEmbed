<?php

define('ROOT', dirname(__DIR__));
define('SMF', true);

function loadLanguage(){}
function log_error(){}

// Mock SMFs settings values.
global $modSettings;
global $sourcedir, $scripturl;
global $boarddir, $boardurl, $context;

$modSettings = array(
	'OharaYTEmbed_enable' => true,
	'OharaYTEmbed_autoEmbed' => true,
	'OharaYTEmbed_width' => 480,
	'OharaYTEmbed_height' => 270,
	'OharaYTEmbed_enable_youtube' => true,
	'OharaYTEmbed_enable_vimeo' => true,
	'OharaYTEmbed_enable_gifv' => true,
);

// Lets pretend we are uninstalling a mod...
$context['uninstalling'] = true;

$boarddir = ROOT;
$sourcedir = ROOT . DIRECTORY_SEPARATOR .'Sources';
$scripturl = $boardurl = '';

// Require some files
require_once $sourcedir . DIRECTORY_SEPARATOR .'OharaYTEmbed.php';
require_once $sourcedir . DIRECTORY_SEPARATOR .'iOharaYTEmbed.php';

// Composer-Autoloader
require_once ROOT . DIRECTORY_SEPARATOR .'vendor'. DIRECTORY_SEPARATOR .'autoload.php';
