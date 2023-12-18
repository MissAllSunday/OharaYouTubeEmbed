<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.2.15
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (C) 2023 Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
    die('No direct access...');

function OYTE_bbc_add_code(&$codes)
{
    global $modSettings, $txt;

    if (empty($modSettings['OYTE_master']))
        return;

    loadLanguage('OharaYTEmbed');

    array_push($codes,
        [
            'tag' => 'youtube',
            'type' => 'unparsed_content',
            'content' => '$1',
            'validate' => function (&$tag, &$data, $disabled) use ($txt)
            {
                // This tag was disabled.
                if (!empty($disabled['youtube']))
                    return;

                if (empty($data))
                    $data = $txt['OYTE_unvalid_link'];

                else
                    $data = OYTE_Main(trim(strtr($data, ['<br />' => ''])));
            },
            'disabled_content' => '$1',
            'block_level' => true,
		],
        [
            'tag' => 'yt',
            'type' => 'unparsed_content',
            'content' => '$1',
            'validate' => function (&$tag, &$data, $disabled)
            {
                global $txt;

                // This tag was disabled.
                if (!empty($disabled['yt']))
                    return;

                if (empty($data))
                    $data = $txt['OYTE_unvalid_link'];

                else
                    $data = OYTE_Main(trim(strtr($data, ['<br />' => ''])));
            },
            'disabled_content' => '$1',
            'block_level' => true,
		],
        [
            'tag' => 'vimeo',
            'type' => 'unparsed_content',
            'content' => '$1',
            'validate' => function (&$tag, &$data, $disabled)
            {
                global $txt;

                // This tag was disabled.
                if (!empty($disabled['vimeo']))
                    return;

                if (empty($data))
                    $data = $txt['OYTE_unvalid_link'];

                else
                    $data = OYTE_Vimeo(trim(strtr($data, ['<br />' => ''])));
            },
            'disabled_content' => '$1',
            'block_level' => true,
		]
    );

    OYTE_care();
}

// The bbc button.
function OYTE_bbc_add_button(&$buttons)
{
    global $txt, $modSettings;

    loadLanguage('OharaYTEmbed');

    if (empty($modSettings['OYTE_master']))
        return;

	array_push($buttons, [
		'image' => 'youtube',
		'code' => 'youtube',
		'before' => '[youtube]',
		'after' => '[/youtube]',
		'description' => $txt['OYTE_desc'],
	], [
		'image' => 'vimeo',
		'code' => 'vimeo',
		'before' => '[vimeo]',
		'after' => '[/vimeo]',
		'description' => $txt['OYTE_vimeo_desc'],
	]);
}

// Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^.
function OYTE_settings(&$config_vars)
{
    global $txt;

    loadLanguage('OharaYTEmbed');

    $config_vars[] = $txt['OYTE_title'];
    $config_vars[] = array('check', 'OYTE_master', 'subtext' => $txt['OYTE_master_sub']);
    $config_vars[] = array('check', 'OYTE_autoEmbed', 'subtext' => $txt['OYTE_autoEmbed_sub']);
    $config_vars[] = array('int', 'OYTE_min_screen_size', 'subtext' => $txt['OYTE_min_screen_size_sub'], 'size' => 4);
    $config_vars[] = array('int', 'OYTE_video_width', 'subtext' => $txt['OYTE_video_width_sub'], 'size' => 4);
    $config_vars[] = array('int', 'OYTE_video_height', 'subtext' => $txt['OYTE_video_height_sub'], 'size' => 4);
    $config_vars[] = '';
}

// Take the url, take the video ID and return the embed code.
function OYTE_Main($data)
{
    global $modSettings, $txt;

    loadLanguage('OharaYTEmbed');

    // Gotta respect the master setting...
    if (empty($data) || empty($modSettings['OYTE_master']))
        return sprintf($txt['OYTE_unvalid_link'], 'youtube');

    // Set a local var for laziness.
    $videoID = '';
    $result = '';

    // Check if the user provided the youtube ID
    if (preg_match('/^[a-zA-z0-9_-]{11}$/', $data) > 0)
        $videoID = $data;

    // We all love Regex.
    $pattern = '#(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([\w-]{11}) (?:[^\s]+) (?:[ \t\r\n])#xi';

    // First attempt, pure regex.
    if (empty($videoID) && preg_match($pattern, $data, $matches))
        $videoID = $matches[1] ?? false;

    // Give another regex a chance.
    elseif (empty($videoID) && preg_match('%(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})%i', $data, $match))
        $videoID = $match[1] ?? false;

    // No?, then one last chance, let PHPs native parse_url() function do the dirty work.
    elseif (empty($videoID))
    {
        // This relies on the url having ? and =, this is only an emergency check.
        parse_str(parse_url($data, PHP_URL_QUERY), $videoID);
        $videoID = $videoID['v'] ?? false;
    }

    // At this point, all tests had miserably failed.
    if (empty($videoID))
        return sprintf($txt['OYTE_unvalid_link'], 'youtube');

    // Got something!
    else
        $result = '<div class="oharaEmbed youtube" id="oh_'. $videoID .'"><noscript><a href="https://youtube.com/watch?v='. $videoID .'">https://youtube.com/watch?v='. $videoID . '</a></noscript></div>';

    return $result;
}

function OYTE_Vimeo($data)
{
    global $modSettings, $txt;

    if (empty($data) || empty($modSettings['OYTE_master']))
        return sprintf($txt['OYTE_unvalid_link'], 'vimeo');

    loadLanguage('OharaYTEmbed');


    // Construct the URL
    $oembed = 'https://vimeo.com/api/oembed.json?dnt=true&url=' . rawurlencode($data);
    $jsonArray = json_decode(curlWrapper($oembed), true);

    if (!empty($jsonArray) && is_array($jsonArray) && !empty($jsonArray['html']))
        return '<div class="oharaEmbed vimeo">'. $jsonArray['html'].'</div>';

    else
        return sprintf($txt['OYTE_unvalid_link'], 'vimeo');
}

function OYTE_Preparse($message)
{
    global $context, $modSettings;

    // Gotta respect the master and the autoembed setting.
    if (empty($modSettings['OYTE_master']) || empty($modSettings['OYTE_autoEmbed']))
        return $message;

    // Someone else might not like this!
    if (empty($message) || !empty($context['ohara_disable']))
        return $message;

    // The extremely long regex...
    $vimeo = '~(?<=[\s>\.(;\'"]|^)(?:https?\:\/\/)?(?:www\.)?vimeo.com\/(?:album\/|groups\/(.*?)\/|channels\/(.*?)\/)?[0-9]+\??[/\w\-_\~%@\?;=#}\\\\]?~';
    $youtube = '~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([\w-]{11})(?:[^\s|\<|\[]+)?(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>|<\/a> ))[?=&+%\w.-]*[\/\w\-_\~%@\?;=#}\\\\]?~ix';

    $gifv = '~(?<=[\s>\.(;\'"]|^)(?:http|https):\/\/[\w\-_%@:|]?(?:www\.)?i\.imgur\.com\/([a-z0-9]+)\.(?:gif|gifv|webm|mp4)(?=[^\w-]|$)(?![?=&+%\w.-]*(?:[\'"][^<>]*>  | <\/a>  ))[?=&+%\w.-]*[\/\w\-_\~%@\?;=#}\\\\]?~ix';

    return preg_replace_callback_array(
        [
            $vimeo => function ($match) {
                return OYTE_Vimeo($match[0]);
            },
            $youtube => function ($match) {
                return OYTE_Main($match[1]);
            },
            $gifv => function ($match) {
                return '[gifv]'. $match[1] .'[/gifv]';
            },
        ],
        $message
    );
}

function OYTE_css()
{
    global $context, $settings, $modSettings;

    $videoWidth = !empty($modSettings['OYTE_video_width']) ? $modSettings['OYTE_video_width'] : 480;
    $videoHeight = !empty($modSettings['OYTE_video_height']) ? $modSettings['OYTE_video_height'] : 270;
    $screenMinSize = !empty($modSettings['OYTE_min_screen_size']) ? $modSettings['OYTE_min_screen_size'] : 768;

    // Add our css and js files. Dear and lovely mod authors, if you're going to use $context['html_headers'] MAKE SURE you append your data .= instead of re-declaring the var! and don't forget to add a new line and proper indentation too!
    $context['html_headers'] .= '
    <script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/ohyoutube.js"></script>
    <link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/oharaEmbed.css" />
    <style>
        @media screen and (min-width: '. $screenMinSize .'px) {
            .oharaEmbed, .gifv, .oharaEmbed iframe, .oharaEmbed object, .oharaEmbed embed {
                max-width: '. $videoWidth .'px;
                max-height: '. $videoHeight .'px;
                padding-bottom: '. $videoHeight .'px;
            }
        }
    </style>';
}

// DUH! WINNING!
function OYTE_care()
{
    global $context;

    if (!empty($context['current_action']) && $context['current_action'] == 'credits')
        $context['copyrights']['mods'][] = '
        <a href="https://missallsunday.com" target="_blank" title="Free SMF mods">
            Ohara YouTube Embed mod &copy Suki</a>';
}

function curlWrapper($url) {

    if(function_exists('curl_init') === false){
        return '';
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}