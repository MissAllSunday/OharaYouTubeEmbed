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
            public const REGEX = '%https://mocksite\\.com/video/\\K\\d+|^mock-\\d+$%i';
            public const AUTO_REGEX = '%https://mocksite\\.com/video/\\d+%i';
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

        global $modSettings;
        $modSettings[OharaYTEmbed::PATTERN . 'width'] = 480;
        $modSettings[OharaYTEmbed::PATTERN . 'height'] = 270;
    }

    public function testContentPipelineReturnsTemplateWithOembedData(): void
    {
        $result = $this->videoProvider->content('https://mocksite.com/video/123');

        $this->assertStringContainsString('class="oharaEmbed mocksite"', $result);

        $expectedEmbed = rawurlencode('https://mocksite.com/embed/123');
        $this->assertStringContainsString('data-ohara_embed_url="' . $expectedEmbed . '"', $result);
    }

    public function testContentPipelineHandlesOembedFailureSecurely(): void
    {
        $result = $this->videoProvider->content('mock-999');

        $this->assertStringContainsString('class="oharaEmbed mocksite"', $result);
        $this->assertStringContainsString('title="Mocksite"', $result);
        $this->assertStringContainsString('data-ohara_embed_url="https://mocksite.com/embed/mock-999"', $result);
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

        $this->videoProvider->auto($message);

        $this->assertStringContainsString('class="oharaEmbed mocksite"', $message);
        $this->assertStringContainsString('data-ohara_embed_url="https://mocksite.com/embed/123"', $message);
        $this->assertStringNotContainsString('https://mocksite.com/video/123', $message);
    }
}