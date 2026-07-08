<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Site\VideoProvider;

/**
 * Imgur gifv / webm / gallery embed site with oEmbed support.
 * Handles BBC tag [gifv]…[/gifv].
 */
final class GifvSite extends VideoProvider
{
    public function getIdentifier(): string
    {
        return 'gifv';
    }

    public function getRegex(): string
    {
        return '%(?:https?://)?(?:www\.|i\.)?imgur\.com/(?:a/|gallery/)?\K[a-zA-Z0-9]{5,10}(?=\.(?:gifv|webm|mp4|gif))?|([a-zA-Z0-9]{5,10})$%ix';
    }

    public function getAutoRegex(): string
    {
        return '%(?:^|[^\[])\K(?:https?://)?(?:www\.|i\.)?imgur\.com/(?:a/|gallery/)?[a-zA-Z0-9]{5,10}%ix';
    }

    public function getEmbedUrl(): string
    {
        return 'https://imgur.com/a/{video_id}/embed?pub=true';
    }

    public function getRequestUrl(): string
    {
        return 'https://imgur.com/a/{video_id}';
    }

    public function getOembedUrl(): string
    {
        return 'https://api.imgur.com/oembed.json?url={url}';
    }

    public function getDefaultThumbUrl(): string
    {
        return 'https://s.imgur.com/images/favicon-96x96.png';
    }
}