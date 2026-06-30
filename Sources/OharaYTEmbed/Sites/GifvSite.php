<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Site\VideoProvider;

/**
 * Imgur gifv / webm embed site.
 *
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
        return '%(?:https?://)?(?:www\.)?i\.imgur\.com/\K[a-zA-Z0-9]+(?=\.(?:gifv|webm))|^[a-zA-Z0-9]{5,10}$%ix';
    }

    public function getAutoRegex(): string
    {
        return '%(?:^|[^\[])\K(?:https?://)?(?:www\.)?i\.imgur\.com/[a-zA-Z0-9]+(?=\.(?:gifv|webm))%ix';
    }

    public function getEmbedUrl(): string
    {
        return '';
    }

    public function getRequestUrl(): string
    {
        return '';
    }

    public function getOembedUrl(): string
    {
        return '';
    }

    public function getTemplate(): string
    {
        return '<div class="oharaEmbed {id}" data-ohara_{id}="{data_json}" id="oh_{id}_{video_id}" style="width: {width}px; height: {height}px;">'
            . '<video preload="auto" autoplay="autoplay" loop="loop" muted="muted" playsinline="playsinline" style="width: 100%; height: 100%; max-width: {width}px; max-height: {height}px;">'
            . '<source src="//i.imgur.com/{video_id}.webm" type="video/webm">'
            . '<source src="//i.imgur.com/{video_id}.mp4" type="video/mp4">'
            . '</video>'
            . '</div>';
    }
}