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
            public const IDENTIFIER = 'youtube';
            public const REGEX = '%(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/(?:embed/|v/|watch\?(?:[^&]*&)*?v=)([\w-]{11})|(?:https?://)?youtu\.be/([\w-]{11})%i';
            public const AUTO_REGEX = '%(?:https?://)?(?:www\.|m\.)?youtube(?:-nocookie)?\.com/(?:embed/|v/|watch\?(?:[^&]*&)*?v=)[\w-]{11}|(?:https?://)?youtu\.be/[\w-]{11}%i';
            public const EMBED_URL = 'https://youtube.com/embed/{video_id}?autoplay=1&autohide=1';
            public const REQUEST_URL = 'https://youtube.com/watch?v={video_id}';
            public const OEMBED_URL = 'https://www.youtube.com/oembed?url={url}&format=json';
            public const BUTTON_IMAGE = 'button.png';

            protected function fetchOembedResponse(string $videoId): string|false
            {
                return json_encode([
                    EmbedParams::KEY_VIDEO_ID => $videoId,
                    EmbedParams::KEY_TITLE => $this->getDisplayName(),
                    EmbedParams::KEY_THUMBNAIL_URL => 'https://example.com/thumbnail.jpg',
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
        $videoId = 'MBdfBTXWtFo';
        $embedHtml = $this->videoProvider->content("https://www.youtube.com/watch?v={$videoId}");

        $this->assertStringContainsString('id="oh_youtube_MBdfBTXWtFo"', $embedHtml);
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
        global $txt;

        $txt[OharaYTEmbed::PATTERN . 'invalid_link'] = 'Invalid {site} link';
        $result = $this->videoProvider->invalid();
        $this->assertSame('Invalid Youtube link', $result);

        unset($txt[OharaYTEmbed::PATTERN . 'invalid_link']);
    }

    // -----------------------------------------------------------------------
    // auto()
    // -----------------------------------------------------------------------

    public function testAutoReplacesValidVideoUrls(): void
    {
        $message = "Check out https://www.youtube.com/watch?v=MBdfBTXWtFo and https://example.com/video/invalid";
        $this->videoProvider->auto($message);

        $this->assertStringContainsString('id="oh_youtube_MBdfBTXWtFo"', $message);
        $this->assertStringContainsString('invalid', $message);
    }

    public function testAutoDoesNotReplaceInvalidVideoUrls(): void
    {
        $message = "Check out https://example.com/video/invalid";
        $this->videoProvider->auto($message);
        $this->assertSame("Check out https://example.com/video/invalid", $message);
    }
}
