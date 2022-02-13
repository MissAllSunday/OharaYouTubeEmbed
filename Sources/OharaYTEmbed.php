<?php

/*
 * @package Ohara Youtube Embed mod
 * @version 1.2.12
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (C) 2022 Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
    die('Hacking attempt...');

function OYTE_bbc_add_code(&$codes)
{
    global $modSettings, $txt;

    if (empty($modSettings['OYTE_master']))
        return;

    loadLanguage('OharaYTEmbed');

    array_push($codes,
        array(
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
                    $data = OYTE_Main(trim(strtr($data, array('<br />' => ''))));
            },
            'disabled_content' => '$1',
            'block_level' => true,
        ),
        array(
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
                    $data = OYTE_Main(trim(strtr($data, array('<br />' => ''))));
            },
            'disabled_content' => '$1',
            'block_level' => true,
        ),
        array(
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
                    $data = OYTE_Vimeo(trim(strtr($data, array('<br />' => ''))));
            },
            'disabled_content' => '$1',
            'block_level' => true,
        ),
        array(
            'tag' => 'gifv',
            'type' => 'unparsed_content',
            'content' => '$1',
            'validate' => function (&$tag, &$data, $disabled)
            {
                global $txt;

                // This tag was disabled.
                if (!empty($disabled['gifv']))
                    return;

                if (empty($data))
                    $data = $txt['OYTE_unvalid_link'];

                else
                    $data = OYTE_Gifv(trim(strtr($data, array('<br />' => ''))));
            },
            'disabled_content' => '$1',
            'block_level' => true,
        )
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

    $buttons[count($buttons) - 1][] = array(
        'image' => 'youtube',
        'code' => 'youtube',
        'before' => '[youtube]',
        'after' => '[/youtube]',
        'description' => $txt['OYTE_desc'],
    );

    $buttons[count($buttons) - 1][] =array(
        'image' => 'vimeo',
        'code' => 'vimeo',
        'before' => '[vimeo]',
        'after' => '[/vimeo]',
        'description' => $txt['OYTE_vimeo_desc'],
    );

    $buttons[count($buttons) - 1][] =array(
        'image' => 'gifv',
        'code' => 'gifv',
        'before' => '[gifv]',
        'after' => '[/gifv]',
        'description' => $txt['OYTE_gifv_desc'],
    );
}

// Don't bother on create a whole new page for this, let's use integrate_general_mod_settings ^o^.
function OYTE_settings(&$config_vars)
{
    global $txt;

    loadLanguage('OharaYTEmbed');

    $config_vars[] = $txt['OYTE_title'];
    $config_vars[] = array('check', 'OYTE_master', 'subtext' => $txt['OYTE_master_sub']);
    $config_vars[] = array('check', 'OYTE_autoEmbed', 'subtext' => $txt['OYTE_autoEmbed_sub']);
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
        $videoID = isset($matches[1]) ? $matches[1] : false;

    // Give another regex a chance.
    elseif (empty($videoID) && preg_match('%(?:youtube(?:-nocookie)?\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})%i', $data, $match))
        $videoID = isset($match[1]) ? $match[1] : false;

    // No?, then one last chance, let PHPs native parse_url() function do the dirty work.
    elseif (empty($videoID))
    {
        // This relies on the url having ? and =, this is only an emergency check.
        parse_str(parse_url($data, PHP_URL_QUERY), $videoID);
        $videoID = isset($videoID['v']) ? $videoID['v'] : false;
    }

    // At this point, all tests had miserably failed.
    if (empty($videoID))
        return sprintf($txt['OYTE_unvalid_link'], 'youtube');

    // Got something!
    else
        $result = '
		<div class="oharaEmbed youtube" id="oh_'. $videoID .'">
			<noscript>
				<a href="//www.youtube.com/watch?v='. $videoID .'">//www.youtube.com/watch?v='. $videoID . '</a>
			</noscript>
		</div>';

    return $result;
}

function OYTE_Vimeo($data)
{
    global $modSettings, $txt, $sourcedir;

    if (empty($data) || empty($modSettings['OYTE_master']))
        return sprintf($txt['OYTE_unvalid_link'], 'vimeo');

    loadLanguage('OharaYTEmbed');

    // Need a function in a far far away file...
    require_once($sourcedir .'/Subs-Package.php');

    // Construct the URL
    $oembed = 'https://vimeo.com/api/oembed.json?url=' . rawurlencode($data) . '&width='.
        (empty($modSettings['OYTE_video_width']) ? '480' : $modSettings['OYTE_video_width']) .
        '&height='. (empty($modSettings['OYTE_video_height']) ? '270' : $modSettings['OYTE_video_height']);

    //Attempts to fetch data from a URL, regardless of PHP's allow_url_fopen setting
    $jsonArray = json_decode(fetch_web_data($oembed), true);

    if (!empty($jsonArray) && is_array($jsonArray) && !empty($jsonArray['html']))
        return '
        <div class="oharaEmbed vimeo">'. str_replace('<iframe', '<iframe width="'.
                (empty($modSettings['OYTE_video_width']) ? '480' : $modSettings['OYTE_video_width']) .
                'px" height="'. (empty($modSettings['OYTE_video_height']) ? '270' : $modSettings['OYTE_video_height']) .
                'px"', $jsonArray['html']) .'</div>';

    else
        return sprintf($txt['OYTE_unvalid_link'], 'vimeo');
}

function OYTE_Gifv($data)
{
    global $modSettings, $txt;

    loadLanguage('OharaYTEmbed');

    // Gotta respect the master setting...
    if (empty($data) || empty($modSettings['OYTE_master']))
        return sprintf($txt['OYTE_unvalid_link'], 'gifv');

    // Set a local var for laziness.
    $videoID = '';
    $result = '';

    if (strpos($data, 'http') === false || strpos($data, '.com') === false)
        return '
		<video class="oharaEmbed gifv" autoplay loop preload="auto" controls>
			<source src="https://i.imgur.com/'. $data .'.webm" type="video/webm">
			<source src="https://i.imgur.com/'. $data .'.mp4" type="video/mp4">
		</video>';


    // We all love Regex.
    $pattern = '/^(?:https?:\/\/)?(?:www\.)?i\.imgur\.com\/([a-z0-9]+)\.(?:gif|gifv|webm|mp4)/i';

    // First attempt, pure regex.
    if (empty($videoID) && preg_match($pattern, $data, $matches))
        $videoID = isset($matches[1]) ? $matches[1] : false;


    // At this point, all tests had miserably failed.
    if (empty($videoID))
        return sprintf($txt['OYTE_unvalid_link'], 'gifv');

    // Got something!
    else
        $result = '
		<video class="gifv" autoplay loop preload="auto" controls>
			<source src="https://i.imgur.com/'. $videoID .
            '.webm" type="video/webm">
			<source src="https://i.imgur.com/' .
            $videoID .'.mp4" type="video/mp4">
		</video>';

    return $result;
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

    // Is this a YouTube video url?
    $message = preg_replace_callback(
        $youtube,
        function ($matches) {
            return '[youtube]'. $matches[1] .'[/youtube]';
        },
        $message
    );

    // A Vimeo url perhaps?
    $message = preg_replace_callback(
        $vimeo,
        function ($matches) {
            return '[vimeo]'. $matches[0] .'[/vimeo]';
        },
        $message
    );

    // imgur gifv format.
    return preg_replace_callback(
        $gifv,
        function ($matches) {
            return '[gifv]'. $matches[1] .'[/gifv]';
        },
        $message
    );
}

function OYTE_css()
{
    global $context, $settings, $modSettings;

    // Add our css and js files. Dear and lovely mod authors, if you're going to use $context['html_headers'] MAKE SURE you append your data .= instead of re-declaring the var! and don't forget to add a new line and proper indentation too!
    $context['html_headers'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/ohyoutube.js"></script>
	<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/oharaEmbed.css" />';
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
