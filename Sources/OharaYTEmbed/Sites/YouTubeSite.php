<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Site\VideoProvider;

/**
 * YouTube / youtu.be embed site with oEmbed support.
 *
 * Handles BBC tags [youtube]…[/youtube]
 * Resolves video metadata (title, thumbnail) via YouTube's oEmbed JSON API
 * using SMF's fetch_web_data() helper.
 */
final class YouTubeSite extends VideoProvider
{
    public const IDENTIFIER = 'youtube';
    public const REGEX = '%(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/(?:embed/|v/|watch\?(?:[^&]*&)*?v=)([\w-]{11})|(?:https?://)?youtu\.be/([\w-]{11})%i';
    public const AUTO_REGEX = '%(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/(?:embed/|v/|watch\?(?:[^&]*&)*?v=)[\w-]{11}|(?:https?://)?youtu\.be/[\w-]{11}%i';
    public const EMBED_URL = 'https://youtube.com/embed/{video_id}?autoplay=1&autohide=1';
    public const REQUEST_URL = 'https://youtube.com/watch?v={video_id}';
    public const OEMBED_URL = 'https://www.youtube.com/oembed?url={url}&format=json';
    public const BUTTON_IMAGE = 'data:image/gif;base64,R0lGODlhEAARAOMMAP//////zP//AMz//8wAAMwAAID/AIBAQEBAQAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAwALAAAAAAQABEAAwRHMDlJq70468076F5YgGRAKIIgEMJAuGzrvnBs33it33iu7/zAgHBILBqPyKRyyWw6n9CodEqtWq/YrHbL7XrB4LBIFAIAOw==';
}