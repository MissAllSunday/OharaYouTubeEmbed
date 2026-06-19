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

    public const REGEX = '';
    public const IDENTIFIER = '';
    public const EMBED_URL = '';
    public const REQUEST_URL = '';
    public const OEMBED_URL = '';
    public const BUTTON_IMAGE = '';

    public function getTemplate(): string
    {
        return '<div class="oharaEmbed {id}" title="{title}" data-ohara_{id}="{video_id}" data-ohara_image_url="{image_url}" id="oh_{id}_{video_id}" style="width: {width}px; height: {height}px;"></div>';
    }

    public function getDisplayName(): string
    {
        return ucfirst(static::IDENTIFIER);
    }

    public function content(string $data): string
    {
        $videoId = $this->extractVideoId($data);

        if ($videoId === '') {
            return $data;
        }

        if (static::OEMBED_URL === '') {
            return $this->create(EmbedParams::from([EmbedParams::KEY_VIDEO_ID => $videoId]));
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
        if (static::REGEX === '') {
            return;
        }

        if (preg_match_all(static::REGEX, $message, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $urlToReplace = $match[0];

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
        if (strlen($cleanData) === 11 || (is_numeric($cleanData) && strlen($cleanData) >= 6)) {
            return $cleanData;
        }

        if (static::REGEX !== '' && preg_match(static::REGEX, $cleanData, $m)) {
            return $m[0];
        }

        return '';
    }

    protected function fetchOembedResponse(string $videoId): string|false
    {
        if (!function_exists('fetch_web_data')) {
            require_once $this->global('sourcedir') . '/Subs-Package.php';
        }

        $videoUrl = $this->tokens(static::REQUEST_URL, [EmbedParams::KEY_VIDEO_ID => $videoId]);

        $oembedUrl = $this->tokens(static::OEMBED_URL, [
            'url'    => rawurlencode($videoUrl),
            'width'  => $this->getSetting('width', OharaYTEmbed::DEFAULT_WIDTH),
            'height' => $this->getSetting('height', OharaYTEmbed::DEFAULT_HEIGHT),
        ]);

        return fetch_web_data($oembedUrl);
    }

    protected function processOembedResponse(string $response, string $videoId): ?string
    {
        /** @var array<string, mixed>|null $json */
        $json = json_decode($response, true);

        if (!is_array($json) || empty($json)) {
            return null;
        }

        if (empty($json[EmbedParams::KEY_VIDEO_ID])) {
            $json[EmbedParams::KEY_VIDEO_ID] = $videoId;
        }

        return $this->create(EmbedParams::from($json));
    }

    protected function handleFailure(string $videoId): string
    {
        return $this->create(EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID => $videoId,
            EmbedParams::KEY_TITLE    => ucfirst(static::IDENTIFIER) . ' Video', // Uso de constante
        ]));
    }

    protected function create(EmbedParams $params): string
    {
        $params = $params->withDimensions(
            $params->width ?? (int) $this->getSetting(EmbedParams::KEY_WIDTH, OharaYTEmbed::DEFAULT_WIDTH),
            $params->height ?? (int) $this->getSetting(EmbedParams::KEY_HEIGHT, OharaYTEmbed::DEFAULT_HEIGHT)
        );

        return $this->tokens($this->getTemplate(), $params->toArray());
    }
}