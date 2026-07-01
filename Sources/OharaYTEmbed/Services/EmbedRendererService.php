<?php

declare(strict_types=1);

namespace OharaYTEmbed\Services;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\OharaYTEmbed;

class EmbedRendererService
{
    public function render(EmbedSiteInterface $site, EmbedParams $params): string
    {
        $params = $params->withDimensions(
            (int) $site->getSetting(EmbedParams::KEY_WIDTH, OharaYTEmbed::DEFAULT_WIDTH),
            (int) $site->getSetting(EmbedParams::KEY_HEIGHT, OharaYTEmbed::DEFAULT_HEIGHT)
        );

        return $site->tokens($site->getTemplate(), $params->toArray());
    }

    public function renderFailure(EmbedSiteInterface $site, string $videoId): string
    {
        return $this->render($site, EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID   => $videoId,
            EmbedParams::KEY_IDENTIFIER => $site->getIdentifier(),
            EmbedParams::KEY_TITLE      => $site->getDisplayName(),
            EmbedParams::KEY_EMBED_URL  => str_replace('{video_id}', $videoId, $site->getEmbedUrl()),
        ]));
    }
}