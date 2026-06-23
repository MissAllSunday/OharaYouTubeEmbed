<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Sources\OharaYTEmbed\Site;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\Site\VideoProvider;
use PHPUnit\Framework\TestCase;

class VideoProviderTest extends TestCase
{
    private VideoProvider $videoProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->videoProvider = new class() extends VideoProvider {
            public const REGEX = '/regex/';
            public const IDENTIFIER = 'test';
            public const EMBED_URL = 'https://example.com/embed/{video_id}';
            public const REQUEST_URL = 'https://example.com/request/{video_id}';
            public const OEMBED_URL = 'https://example.com/oembed?url={url}&width={width}&height={height}';
            public const BUTTON_IMAGE = 'button.png';

            protected function fetchOembedResponse(string $videoId): string|false
            {
                return json_encode([
                    EmbedParams::KEY_VIDEO_ID => $videoId,
                    EmbedParams::KEY_TITLE => 'Test Video',
                    EmbedParams::KEY_IMAGE_URL => 'https://example.com/thumbnail.jpg',
                    EmbedParams::KEY_WIDTH => 640,
                    EmbedParams::KEY_HEIGHT => 360,
                ]);
            }
        };
    }

    // -----------------------------------------------------------------------
    // content()
    // -----------------------------------------------------------------------

    public function testContentReturnsEmbedHtmlForValidVideoId(): void
    {
        $videoId = '1234567890';
        $embedHtml = $this->videoProvider->content("https://example.com/video/{$videoId}");
        $this->assertStringContainsString('Test Video', $embedHtml);
        $this->assertStringContainsString('https://example.com/embed/1234567890', $embedHtml);
    }

    public function testContentReturnsOriginalDataForInvalidVideoId(): void
    {
        $data = "https://example.com/video/invalid";
        $result = $this->videoProvider->content($data);
        $this->assertSame($data, $result);
    }

    // -----------------------------------------------------------------------
    // invalid()
    // -----------------------------------------------------------------------

    public function testInvalidReturnsInvalidLinkMessage(): void
    {
        $GLOBALS['txt']['OharaYTEmbed_invalid_link'] = 'Invalid {site} link';
        $result = $this->videoProvider->invalid();
        $this->assertSame('Invalid Test link', $result);
        unset($GLOBALS['txt']['OharaYTEmbed_invalid_link']);
    }

    // -----------------------------------------------------------------------
    // auto()
    // -----------------------------------------------------------------------

    public function testAutoReplacesValidVideoUrls(): void
    {
        $message = "Check out https://example.com/video/1234567890 and https://example.com/video/invalid";
        $this->videoProvider->auto($message);
        $this->assertStringContainsString('Test Video', $message);
        $this->assertStringContainsString('https://example.com/embed/1234567890', $message);
        $this->assertStringContainsString('invalid', $message);
    }

    public function testAutoDoesNotReplaceInvalidVideoUrls(): void
    {
        $message = "Check out https://example.com/video/invalid";
        $this->videoProvider->auto($message);
        $this->assertSame("Check out https://example.com/video/invalid", $message);
    }
}
