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
    public function getIdentifier(): string { return 'youtube'; }
    public function getRegex(): string { return '%(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/(?:embed/|v/|watch\?(?:[^&]*&)*?v=)\K[\w-]{11}|(?:https?://)?youtu\.be/\K[\w-]{11}|^[\w-]{11}$%i'; }

    public function getAutoRegex(): string
    {
        return '%(?:^|[^\[])\K(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/(?:embed/|v/|watch\?(?:[^&]*&)*?v=)([\w-]{11})(?:&[^\[\]\s<>]*)*|(?:^|[^\[])\K(?:https?://)?youtu\.be/([\w-]{11})(?:\?[^\[\]\s<>]*)*%i';
    }

    public function getEmbedUrl(): string { return 'https://youtube.com/embed/{video_id}?autoplay=1&autohide=1'; }

    public function getRequestUrl(): string { return 'https://youtube.com/watch?v={video_id}'; }

    public function getOembedUrl(): string { return 'https://www.youtube.com/oembed?url={url}&format=json'; }
}