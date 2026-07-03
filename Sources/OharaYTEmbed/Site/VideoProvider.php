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
            'title="{title}" ' .
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

    public function getDefaultThumbUrl(): string
    {
        return '';
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

    public function registerAssets(): void {}

    public function content(string $videoIdOrUrl): string
    {
        $videoId = $this->extractVideoId($videoIdOrUrl);
        if ($videoId === '') {
            $videoId = $videoIdOrUrl;
        }

        $rawResponse = null;
        $oembedUrl = $this->getOembedUrl();

        if ($oembedUrl !== '') {
            $requestUrl = str_replace('{video_id}', $videoId, $this->getRequestUrl());
            $url = str_replace('{url}', urlencode($requestUrl), $oembedUrl);
            $rawResponse = fetch_web_data($url);
        }

        $embedParams = $this->hydrateParams($videoId, $rawResponse);

        if ($rawResponse === null || $rawResponse === false) {
            return $this->renderer->renderFailure($this, $videoId, $embedParams);
        }

        return $this->renderer->render($this, $embedParams);
    }

    private function hydrateParams(string $videoId, string|false|null $rawResponse): EmbedParams
    {
        $videoData = [];

        if (!empty($rawResponse)) {
            $params = $this->oembedService->processResponse((string) $rawResponse, $videoId);
            if ($params !== null) {
                $videoData = $params->toArray();
            }
        }

        $videoData[EmbedParams::KEY_VIDEO_ID] = $videoId;

        if (empty($videoData[EmbedParams::KEY_IDENTIFIER])) {
            $videoData[EmbedParams::KEY_IDENTIFIER] = $this->getIdentifier();
        }

        if (empty($videoData[EmbedParams::KEY_TITLE])) {
            $videoData[EmbedParams::KEY_TITLE] = $this->getDisplayName();
        }

        $rawEmbedUrl = str_replace('{video_id}', $videoId, $this->getEmbedUrl());
        $videoData[EmbedParams::KEY_EMBED_URL] = rawurlencode($rawEmbedUrl);

        $rawThumbnail = $videoData[EmbedParams::KEY_THUMBNAIL_URL] ?? '';
        if (empty($rawThumbnail)) {
            $rawThumbnail = $this->getDefaultThumbUrl($videoId);
        }
        $videoData[EmbedParams::KEY_THUMBNAIL_URL] = rawurlencode((string) $rawThumbnail);

        return EmbedParams::from($videoData);
    }

    public function auto(string &$message): void
    {
        if ($this->getAutoRegex() === '') {
            return;
        }

        if (preg_match_all($this->getAutoRegex(), $message, $matches)) {
            foreach (array_unique($matches[0]) as $urlToReplace) {
                $pos = strpos($message, $urlToReplace);
                if ($pos !== false) {
                    $beforeText = substr($message, 0, $pos);
                    
                    $openedTags = substr_count(strtolower($beforeText), '[' . strtolower($this->getBbcTag()) . ']');
                    $closedTags = substr_count(strtolower($beforeText), '[/' . strtolower($this->getBbcTag()) . ']');
                    

                    if ($openedTags > $closedTags) {
                        continue;
                    }
                }

                $videoId = $this->extractVideoId($urlToReplace);
                if ($videoId === '') {
                    continue;
                }

                $embedHtml = $this->content($videoId);

                if ($embedHtml !== $videoId) {
                    $message = str_replace($urlToReplace, $embedHtml, $message);
                }
            }
        }
    }

    public function extractVideoId(string $url): string
    {
        if (preg_match($this->getRegex(), $url, $matches)) {
            return end($matches);
        }
        return '';
    }
}