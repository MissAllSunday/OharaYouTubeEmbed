<?php

declare(strict_types=1);

define('ROOT', __DIR__);
define('SMF', true);

global $modSettings, $txt, $context;


$modSettings['OYTE_master'] = true;
$modSettings['OYTE_autoEmbed'] = true;
$modSettings['OYTE_video_width'] = 480;
$modSettings['OYTE_video_height'] = 270;
$modSettings['OYTE_min_screen_size'] = 768;
$context['html_headers'] = '';

// Mock SMF functions
function loadLanguage(string $languageFile): void {}