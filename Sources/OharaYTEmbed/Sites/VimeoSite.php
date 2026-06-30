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
    public function getIdentifier(): string { return 'vimeo'; }
    public function getRegex(): string { return '%(?:https?://)?(?:www\.|player\.)?vimeo\.com/(?:channels/[^/]+/|ondemand/[^/]+/|video/|(?:\w+/)*)?\K[0-9]{6,11}(?=\b|(?:\?.*)?$)|^[0-9]{6,11}$%ix'; }
    public function getAutoRegex(): string
    {
        return '%(?:^|[^\[])\K(?:https?://)?(?:www\.|player\.)?vimeo\.com/(?:channels/[^/]+/|ondemand/[^/]+/|video/|(?:\w+/)*)?[0-9]{6,11}(?:(?:\?|\b)[^\[\]\s<>]*)*%ix';
    }
    public function getEmbedUrl(): string { return 'https://player.vimeo.com/video/{video_id}?autoplay=1'; }
    public function getRequestUrl(): string { return 'https://vimeo.com/{video_id}'; }
    public function getOembedUrl(): string { return 'https://vimeo.com/api/oembed.json?url={url}&width={width}&height={height}'; }
}