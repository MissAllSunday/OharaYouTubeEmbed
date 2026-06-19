<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Site\VideoProvider;

/**
 * Imgur gifv / webm embed site.
 *
 * Handles BBC tag [gifv]…[/gifv].
 * Accepts either a bare imgur ID (e.g. "joGlU0z") or a full imgur URL
 * (e.g. "http://i.imgur.com/joGlU0z.gifv").
 */
final class GifvSite extends VideoProvider
{
    public const IDENTIFIER   = 'gifv';
    public const REGEX        = '%^(?:https?://)?(?:www\.)?i\.imgur\.com/\\K([a-zA-Z0-9]+)(?=\\.(?:gifv|webm))%ix';
    public const BUTTON_IMAGE = 'data:image/gif;base64,R0lGODlhEAARAOMMAP//////zP//AMz//8wAAMwAAID/AIBAQEBAQAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAwALAAAAAAQABEAAwRFMDlJq70468076F5YgGRAKIIgEMJAuGzrvnBs33it33iu7/zAgHBILBqPyKRyyWw6n9CodEqtWq/YrHbL7XrB4LBIFAIAOw==';

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