<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Site\VideoProvider;

/**
 * Vimeo embed site.
 *
 * Handles BBC tag [vimeo]…[/vimeo].
 * Resolves video metadata (title, thumbnail) via Vimeo's oEmbed JSON API
 * using SMF's fetch_web_data() helper (from Subs-Package.php).
 */
final class VimeoSite extends VideoProvider
{
    public const IDENTIFIER = 'vimeo';
    public const REGEX      = '%(?:https?://)?(?:www\.|player\.)?vimeo\.com/(?:[a-z]+/)*([0-9]{6,11})%ix';
    public const AUTO_REGEX = '%(?:https?://)?(?:www\.|player\.)?vimeo\.com/(?:[a-z]+/)*[0-9]{6,11}%ix';
    public const EMBED_URL = 'https//player.vimeo.com/video/{video_id}?autoplay=1';
    public const REQUEST_URL = 'https://vimeo.com/{video_id}';
    public const OEMBED_URL = 'https://vimeo.com/api/oembed.json?url={url}&width={width}&height={height}';
    public const BUTTON_IMAGE = 'data:image/gif;base64,R0lGODlhEAARAOMMAP//////zP//AMz//8wAAMwAAID/AIBAQEBAQAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAwALAAAAAAQABEAAwREMDlJq70468076F5YgGRAKIIgEMJAuGzrvnBs33it33iu7/zAgHBILBqPyKRyyWw6n9CodEqtWq/YrHbL7XrB4LBIFAIAOw==';
}