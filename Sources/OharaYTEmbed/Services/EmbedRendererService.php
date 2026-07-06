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
}