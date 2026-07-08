<?php

declare(strict_types=1);

namespace OharaYTEmbed\Site;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\Contracts\EmbedEngineInterface;
use OharaYTEmbed\Services\EmbedRendererService;
use OharaYTEmbed\Services\BbcPurgeService;
use OharaYTEmbed\Services\OembedService;
use OharaYTEmbed\Traits\SettingsTrait;

abstract class VideoProvider implements EmbedEngineInterface, EmbedSiteInterface
{
    use SettingsTrait;

    private OembedService $oembedService;
    private BbcPurgeService $purgeService;
    private EmbedRendererService $renderer;

    public function __construct()
    {
        $this->oembedService = new OembedService();
        $this->purgeService = new BbcPurgeService();
        $this->renderer = new EmbedRendererService();
    }

    public function getCustomAspectRatio(): ?string
    {
        return null;
    }

    public function getDefaultThumbUrl(): string
    {
        return '';
    }

    public function getTemplate(): string
    {
        return '<div class="oharaEmbed {id}" ' .
            'title="{title}" ' .
            'data-ohara_video_id="{video_id}" ' .
            'data-ohara_thumbnail_url="{thumbnail_url}" ' .
            'data-ohara_embed_url="{embed_url}" ' .
            'data-ohara_aspect_ratio="{aspect_ratio}" ' .
            'data-ohara_width="{width}" ' .
            'data-ohara_height="{height}" ' .
            'id="oh_{id}_{video_id}"></div>';
    }

    public function getDisplayName(): string
    {
        return ucfirst($this->getIdentifier());
    }

    public function getBbcTag(): string
    {
        return $this->getIdentifier();
    }

    public function content(string $videoIdOrUrl): string
    {
        $videoId = $this->extractVideoId($videoIdOrUrl);
        if ($videoId === '') {
            $videoId = $videoIdOrUrl;
        }

        $rawResponse = null;

        if ($this->getOembedUrl() !== '') {
            $requestUrl = str_replace('{video_id}', $videoId, $this->getRequestUrl());
            $url = str_replace('{url}', urlencode($requestUrl), $this->getOembedUrl());
            $rawResponse = fetch_web_data($url);
        }

        $embedParams = $this->oembedService->hydrateFromResponse($rawResponse ?: null, $videoId, $this);

        return $this->renderer->render($this, $embedParams);
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