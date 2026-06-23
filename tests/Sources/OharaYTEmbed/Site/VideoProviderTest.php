<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Sources\OharaYTEmbed\Site;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Site\VideoProvider;
use PHPUnit\Framework\TestCase;

class VideoProviderTest extends TestCase
{
    private VideoProvider $videoProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->videoProvider = new class() extends VideoProvider {
            public const IDENTIFIER = 'mocksite';
            public const REGEX = '%https://mocksite\.com/video/\K\d+|^mock-\d+$%i';
            public const AUTO_REGEX = '%https://mocksite\.com/video/\d+%i';
            public const EMBED_URL = 'https://mocksite.com/embed/{video_id}';
            public const REQUEST_URL = 'https://mocksite.com/video/{video_id}';
            public const OEMBED_URL = 'https://mocksite.com/oembed?url={url}';

            protected function fetchOembedResponse(string $videoId): string|false
            {
                if ($videoId === 'mock-999' || $videoId === '999') {
                    return false;
                }
                return json_encode([
                    EmbedParams::KEY_VIDEO_ID => $videoId,
                    EmbedParams::KEY_TITLE => 'Mock Title',
                    EmbedParams::KEY_THUMBNAIL_URL => 'https://mocksite.com/thumb.jpg',
                ]);
            }
        };
    }

    public function testExtractVideoIdWithRegexPattern(): void
    {
        $this->assertSame('123', $this->videoProvider->extractVideoId('https://mocksite.com/video/123'));
    }

    public function testContentReturnsOriginalDataIfIdExtractionFails(): void
    {
        $data = 'https://invalid-site.com/abc';
        $this->assertSame($data, $this->videoProvider->content($data));
    }

    public function testContentPipelineWithSuccessfulOembed(): void
    {
        $result = $this->videoProvider->content('mock-123');

        $this->assertStringContainsString('class="oharaEmbed mocksite"', $result);
        $this->assertStringContainsString('title="Mock Title"', $result);
        $this->assertStringContainsString('data-ohara_thumbnail_url="https://mocksite.com/thumb.jpg"', $result);
    }

    public function testContentPipelineHandlesOembedFailureSecurely(): void
    {
        // El ID 999 simula una falla de red en nuestro oEmbed Mock
        $result = $this->videoProvider->content('mock-999');

        $this->assertStringContainsString('class="oharaEmbed mocksite"', $result);
        $this->assertStringContainsString('title="Mocksite"', $result);
    }

    public function testInvalidReturnsFormattedLangMessage(): void
    {
        global $txt;
        $txt[OharaYTEmbed::PATTERN . 'invalid_link'] = 'Enlace de {site} roto';

        $this->assertSame('Enlace de Mocksite roto', $this->videoProvider->invalid());

        unset($txt[OharaYTEmbed::PATTERN . 'invalid_link']);
    }

    public function testAutoMassReplacesMatchesCorrectly(): void
    {
        $message = "Mira esto: https://mocksite.com/video/123 y esto no: https://example.com";

        // Para que content() extraiga el ID en el mock, pasamos un ID directo simulado por el provider secundario
        $this->videoProvider->auto($message);

        $this->assertStringContainsString('class="oharaEmbed mocksite"', $message);
        $this->assertStringNotContainsString('https://mocksite.com/video/123', $message);
        $this->assertStringContainsString('https://example.com', $message);
    }
}