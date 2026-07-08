<?php

declare(strict_types=1);

namespace OharaYTEmbed\Services;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\Contracts\EmbedSiteInterface;

class OembedService
{
    public function hydrateFromResponse(?string $json, string $videoId, EmbedSiteInterface $site): EmbedParams
    {
        $data = [];
        if (!empty($json) && $json !== '0') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        $videoData = [];
        $videoData[EmbedParams::KEY_VIDEO_ID] = $videoId;
        $videoData[EmbedParams::KEY_IDENTIFIER] = $data['provider_name'] ?? $site->getIdentifier();
        $videoData[EmbedParams::KEY_TITLE] = $data['title'] ?? $site->getDisplayName();

        $videoData[EmbedParams::KEY_WIDTH] = isset($data['width']) ? (int) $data['width'] : (int) $site->getSetting('width', 480);
        $videoData[EmbedParams::KEY_HEIGHT] = isset($data['height']) ? (int) $data['height'] : (int) $site->getSetting('height', 270);

        $rawEmbedUrl = str_replace('{video_id}', $videoId, $site->getEmbedUrl());
        $videoData[EmbedParams::KEY_EMBED_URL] = rawurlencode($rawEmbedUrl);

        $rawThumbnail = $data['thumbnail_url'] ?? '';
        if (empty($rawThumbnail)) {
            $rawThumbnail = $site->getDefaultThumbUrl();
        }
        $videoData[EmbedParams::KEY_THUMBNAIL_URL] = rawurlencode((string) $rawThumbnail);

        $customAspect = $site->getCustomAspectRatio();
        if ($customAspect !== null) {
            $videoData[EmbedParams::KEY_ASPECT_RATIO] = $customAspect;
        } else {
            $videoData[EmbedParams::KEY_ASPECT_RATIO] = $videoData[EmbedParams::KEY_WIDTH] . ' / ' . $videoData[EmbedParams::KEY_HEIGHT];
        }

        return EmbedParams::from($videoData);
    }
}