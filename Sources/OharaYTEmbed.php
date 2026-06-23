<?php

/**
 * SMF hook entry-point for OharaYouTubeEmbed.
 *
 * SMF cold-loads this file (via the <hook file="$sourcedir/OharaYTEmbed.php">
 * attribute) before instantiating OharaYTEmbed\OharaYTEmbed and calling the
 * relevant hook method. Its only job is to bootstrap Composer's PSR-4
 * autoloader so that all OharaYTEmbed\* namespaced classes resolve correctly.
 *
 * The actual class lives in Sources/OharaYTEmbed/OharaYTEmbed.php.
 */

$_autoloader = dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists($_autoloader)) {
    require_once $_autoloader;
}

unset($_autoloader);
