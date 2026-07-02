<?php

// Composer PSR-4 autoloader — must come first so all OharaYTEmbed\* classes resolve.
require_once dirname(__DIR__) . '/oharayt_vendor/autoload.php';

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

function fetch_web_data(string $url, mixed $post_data = '', bool $keep_alive = false): string|false
{
    $providerKey = 'default';
    foreach (['youtube', 'vimeo', 'oembed.example'] as $key) {
        if (str_contains(strtolower($url), $key)) {
            $providerKey = $key;
            break;
        }
    }

    return match ($providerKey) {
        'oembed.example' => json_encode([
            'title'         => 'Mock Video Title',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
        ]),
        'youtube' => json_encode([
            'title'         => 'Youtube Video Title',
            'thumbnail_url' => 'https://img.youtube.com/vi/MBdfBTXWtFo/hqdefault.jpg',
        ]),
        'vimeo' => json_encode([
            'title'         => 'Vimeo Video Title',
            'thumbnail_url' => 'https://vsp.vimeocdn.com/images/default.jpg',
        ]),
        default => false,
    };
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
