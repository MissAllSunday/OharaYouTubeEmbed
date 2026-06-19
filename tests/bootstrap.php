<?php

// Composer PSR-4 autoloader — must come first so all OharaYTEmbed\* classes resolve.
require_once dirname(__DIR__) . '/vendor/autoload.php';

define('ROOT', dirname(__DIR__));
define('SMF', true);

// ---------------------------------------------------------------------------
// SMF function stubs — the real SMF functions are not available in the test
// environment. All stubs are no-ops (or return safe defaults) so that the
// OharaYTEmbed class can be instantiated and exercised without a live forum.
// ---------------------------------------------------------------------------

function loadLanguage(string $file = ''): void {}
function log_error(string $msg = '', string $type = 'general'): void {}
function loadCSSFile(string $filename, array $params = [], string $id = ''): void {}
function loadJavaScriptFile(string $filename, array $params = [], string $id = ''): void {}
function addInlineJavaScript(string $js, bool $defer = false): void {}

/**
 * Stub for SMF's fetch_web_data().
 * VimeoSite calls this for oEmbed lookups; returning false causes content()
 * to fall through to invalid() — acceptable for unit tests that do not make
 * live HTTP requests.
 */
function fetch_web_data(string $url, mixed $post_data = '', bool $keep_alive = false): string|false
{
    return false;
}

// ---------------------------------------------------------------------------
// SMF global variables expected by OharaYTEmbed and SettingsTrait
// ---------------------------------------------------------------------------

global $modSettings, $sourcedir, $scripturl, $boarddir, $boardurl, $context, $txt;

$modSettings = [
    'OharaYTEmbed_enable'         => true,
    'OharaYTEmbed_autoEmbed'      => true,
    'OharaYTEmbed_width'          => 480,
    'OharaYTEmbed_height'         => 270,
    'OharaYTEmbed_enable_youtube' => true,
    'OharaYTEmbed_enable_vimeo'   => true,
    'OharaYTEmbed_enable_gifv'    => true,
];

$context   = [];
$txt       = [];
$boarddir  = ROOT;
$sourcedir = ROOT . '/Sources';
$scripturl = '';
$boardurl  = '';
