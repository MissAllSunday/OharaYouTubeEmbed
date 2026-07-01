<?php

declare(strict_types=1);

namespace OharaYTEmbed\Site;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\Services\EmbedRendererService;
use OharaYTEmbed\Traits\SettingsTrait;
use OharaYTEmbed\Services\BbcPurgeService;
use OharaYTEmbed\Services\OembedService;

abstract class VideoProvider implements EmbedSiteInterface
{
    use SettingsTrait;

    private OembedService $oembedService;
    private BbcPurgeService $purgeService;
    private EmbedRendererService $renderer;

    abstract public function getIdentifier(): string;
    abstract public function getRegex(): string;
    abstract public function getAutoRegex(): string;
    abstract public function getEmbedUrl(): string;
    abstract public function getRequestUrl(): string;
    abstract public function getOembedUrl(): string;

    public function __construct()
    {
        $this->oembedService = new OembedService();
        $this->purgeService = new BbcPurgeService();
        $this->renderer = new EmbedRendererService();
    }

    public function getTemplate(): string
    {
        return '<div class="oharaEmbed {id}" ' .
            'title=\"{title}\" ' .
            'data-ohara_video_id="{video_id}" ' .
            'data-ohara_thumbnail_url="{thumbnail_url}" ' .
            'data-ohara_embed_url="{embed_url}" ' .
            'id="oh_{id}_{video_id}" ' .
            'style="width: {width}px; height: {height}px;"></div>';
    }

    public function getDisplayName(): string
    {
        return ucfirst($this->getIdentifier());
    }

    public function getBbcTag(): string
    {
        return $this->getIdentifier();
    }

    public function getExtraBbcTag(): ?string
    {
        return null;
    }

    public function getButtonImage(): string
    {
        return 'oh_' . $this->getIdentifier();
    }

    public function invalid(): string
    {
        return '';
    }

    public function content(string $videoId): string
    {

        if ($this->getOembedUrl() === '') {
            return $this->renderer->renderFailure($this, $videoId);
        }

        $url = str_replace('{video_id}', $videoId, $this->getOembedUrl());
        $response = fetch_web_data($url);

        $params = (new OembedService())->processResponse((string) $response, $videoId);

        if ($params === null) {
            return $this->renderer->renderFailure($this, $videoId);
        }

        $videoData = $params->toArray();
        if (empty($videoData[EmbedParams::KEY_IDENTIFIER])) {
            $videoData[EmbedParams::KEY_IDENTIFIER] = $this->getIdentifier();
        }

        $rawEmbedUrl = str_replace('{video_id}', $videoId, $this->getEmbedUrl());
        $videoData[EmbedParams::KEY_EMBED_URL] = rawurlencode($rawEmbedUrl);

        $rawThumbnail = $videoData[EmbedParams::KEY_THUMBNAIL_URL] ?? '';
        $videoData[EmbedParams::KEY_THUMBNAIL_URL] = rawurlencode((string) $rawThumbnail);

        return $this->renderer->render($this, EmbedParams::from($videoData));
    }
}