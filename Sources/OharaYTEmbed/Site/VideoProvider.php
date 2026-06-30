<?php

declare(strict_types=1);

namespace OharaYTEmbed\Site;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Traits\SettingsTrait;

abstract class VideoProvider implements EmbedSiteInterface
{
    use SettingsTrait;

    abstract public function getIdentifier(): string;
    abstract public function getRegex(): string;
    abstract public function getAutoRegex(): string;
    abstract public function getEmbedUrl(): string;
    abstract public function getRequestUrl(): string;
    abstract public function getOembedUrl(): string;

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

    public function registerAssets(): void {}

    public function content(string $data): string
    {
        $videoId = $this->extractVideoId($data);

        if ($videoId === '') {
            return $data;
        }

        if ($this->getOembedUrl() === '') {
            return $this->create(EmbedParams::from([
                EmbedParams::KEY_IDENTIFIER => $this->getIdentifier(),
                EmbedParams::KEY_VIDEO_ID => $videoId
            ]));
        }

        $response = $this->fetchOembedResponse($videoId);

        if ($response !== false && $response !== '') {
            $embedHtml = $this->processOembedResponse($response, $videoId);
            if ($embedHtml !== null) {
                return $embedHtml;
            }
        }

        return $this->handleFailure($videoId);
    }

    public function invalid(): string
    {
        return $this->tokens(
            $this->getText('invalid_link'),
            ['site' => $this->getDisplayName()],
        );
    }

    public function auto(string &$message): void
    {
        if ($this->getAutoRegex() === '') {
            return;
        }

        if (preg_match_all($this->getAutoRegex(), $message, $matches)) {
            foreach (array_unique($matches[0]) as $urlToReplace) {
                $embedHtml = $this->content($urlToReplace);

                if ($embedHtml !== $urlToReplace) {
                    $message = str_replace($urlToReplace, $embedHtml, $message);
                }
            }
        }
    }

    public function extractVideoId(string $data): string
    {
        $cleanData = trim($data);

        if ($this->getRegex() !== '' && preg_match($this->getRegex(), $cleanData, $m)) {
            return $m[0];
        }

        return '';
    }

    /**
     * Purge this provider's vanilla SMF tags from the global BBC codes array by reference.
     *
     * @param array $codes The global SMF BBC codes array passed by reference.
     */
    public function disableVanillaCode(array &$codes): void
    {
        if (empty($codes)) {
            return;
        }


        $tagsToPurge = [$this->getBbcTag()];
        if ($this->getExtraBbcTag() !== null) {
            $tagsToPurge[] = $this->getExtraBbcTag();
        }


        $codes = array_filter($codes, static function (array $code) use ($tagsToPurge): bool {
            return !in_array($code['tag'], $tagsToPurge, true);
        });


        $codes = array_values($codes);
    }

    public function disableVanillaTag(): void
    {
        global $context;

        if (empty($context['bbc_tags']) || !is_array($context['bbc_tags'])) {
            return;
        }

        foreach ($context['bbc_tags'] as $rowIndex => $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $buttonIndex => $button) {
                if (isset($button['code']) && $button['code'] === $this->getIdentifier()) {
                    unset($context['bbc_tags'][$rowIndex][$buttonIndex]);
                    break 2;
                }
            }
        }
    }

    protected function fetchOembedResponse(string $videoId): string|false
    {
        if (!function_exists('fetch_web_data')) {
            require_once $this->global('sourcedir') . '/Subs-Package.php';
        }

        $videoUrl = $this->tokens($this->getRequestUrl(), [EmbedParams::KEY_VIDEO_ID => $videoId]);

        $oembedUrl = $this->tokens($this->getOembedUrl(), [
            'url'    => rawurlencode($videoUrl),
            'width'  => $this->getSetting('width', OharaYTEmbed::DEFAULT_WIDTH),
            'height' => $this->getSetting('height', OharaYTEmbed::DEFAULT_HEIGHT),
        ]);

        return fetch_web_data($oembedUrl);
    }

    protected function processOembedResponse(string $response, string $videoId): ?string
    {
        /** @var array<string, mixed>|null $videoData */
        $videoData = json_decode($response, true);

        if (!is_array($videoData) || empty($videoData)) {
            return null;
        }

        if (empty($videoData[EmbedParams::KEY_VIDEO_ID])) {
            $videoData[EmbedParams::KEY_VIDEO_ID] = $videoId;
        }

        if (empty($videoData[EmbedParams::KEY_IDENTIFIER])) {
            $videoData[EmbedParams::KEY_IDENTIFIER] = $this->getIdentifier();
        }

        $rawEmbedUrl = str_replace('{video_id}', $videoId, $this->getEmbedUrl());
        $videoData[EmbedParams::KEY_EMBED_URL] = rawurlencode($rawEmbedUrl);

        $rawThumbnail = $videoData[EmbedParams::KEY_THUMBNAIL_URL] ?? '';
        $videoData[EmbedParams::KEY_THUMBNAIL_URL] = rawurlencode((string) $rawThumbnail);

        return $this->create(EmbedParams::from($videoData));
    }

    protected function handleFailure(string $videoId): string
    {
        return $this->create(EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID => $videoId,
            EmbedParams::KEY_IDENTIFIER => $this->getIdentifier(),
            EmbedParams::KEY_TITLE    => $this->getDisplayName(),
            EmbedParams::KEY_EMBED_URL => str_replace('{video_id}', $videoId, $this->getEmbedUrl()),
            EmbedParams::KEY_WIDTH      => $this->getSetting('width', OharaYTEmbed::DEFAULT_WIDTH),
            EmbedParams::KEY_HEIGHT     => $this->getSetting('height', OharaYTEmbed::DEFAULT_HEIGHT),
        ]));
    }

    protected function create(EmbedParams $params): string
    {
        $params = $params->withDimensions(
            (int) $this->getSetting(EmbedParams::KEY_WIDTH, OharaYTEmbed::DEFAULT_WIDTH),
            (int) $this->getSetting(EmbedParams::KEY_HEIGHT, OharaYTEmbed::DEFAULT_HEIGHT)
        );

        return $this->tokens($this->getTemplate(), $params->toArray());
    }
}