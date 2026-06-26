<?php

declare(strict_types=1);

namespace OharaYTEmbed\Data;

final class EmbedParams
{
    public const KEY_IDENTIFIER = 'id';
    public const KEY_VIDEO_ID  = 'video_id';
    public const KEY_TITLE     = 'title';
    public const KEY_THUMBNAIL_URL = 'thumbnail_url';
    public const KEY_EMBED_URL     = 'embed_url';
    public const KEY_WIDTH     = 'width';
    public const KEY_HEIGHT    = 'height';
    public const KEY_EXTRA     = 'extra';

    public function __construct(
        public string $identifier,
        public string $videoId,
        public string $title = '',
        public string $thumbnailUrl = '',
        public string $embedUrl = '',
        public ?int $width = null,
        public ?int $height = null,
        public array $extra = []
    ) {}

    public static function from(array $data): self
    {
        return new self(
            identifier: (string) ($data[self::KEY_IDENTIFIER] ?? ''),
            videoId:      (string) ($data[self::KEY_VIDEO_ID] ?? ''),
            title:         (string) ($data[self::KEY_TITLE] ?? ''),
            thumbnailUrl:  (string) ($data[self::KEY_THUMBNAIL_URL] ?? ''),
            embedUrl:     (string) ($data[self::KEY_EMBED_URL] ?? ''),
            width:         isset($data[self::KEY_WIDTH]) ? (int) $data[self::KEY_WIDTH] : null,
            height:        isset($data[self::KEY_HEIGHT]) ? (int) $data[self::KEY_HEIGHT] : null,
            extra:         (array) ($data[self::KEY_EXTRA] ?? [])
        );
    }

    public function withDimensions(int $width, int $height): self
    {
        $clone = clone $this;
        $clone->width = $width;
        $clone->height = $height;

        return $clone;
    }

    public function toArray(): array
    {
        return [
            self::KEY_IDENTIFIER => $this->identifier,
            self::KEY_VIDEO_ID  => $this->videoId,
            self::KEY_TITLE     => $this->title,
            self::KEY_THUMBNAIL_URL => $this->thumbnailUrl,
            self::KEY_EMBED_URL     => $this->embedUrl,
            self::KEY_WIDTH     => $this->width,
            self::KEY_HEIGHT    => $this->height,
            self::KEY_EXTRA     => $this->extra,
        ];
    }
}