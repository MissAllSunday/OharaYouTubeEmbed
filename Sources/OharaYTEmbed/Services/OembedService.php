<?php

declare(strict_types=1);

namespace OharaYTEmbed\Services;

use OharaYTEmbed\Data\EmbedParams;

class OembedService
{
    public function processResponse(string $json, string $videoId): ?EmbedParams
    {
        if ($json === '' || $json === '0') {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data)) {
            return null;
        }

        return EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID     => $videoId,
            EmbedParams::KEY_TITLE        => $data['title'] ?? '',
            EmbedParams::KEY_THUMBNAIL_URL => $data['thumbnail_url'] ?? '',
        ]);
    }
}