<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Sources\OharaYTEmbed\Sites;

use OharaYTEmbed\Sites\YouTubeSite;
use PHPUnit\Framework\TestCase;

class YouTubeSiteTest extends TestCase
{
    private YouTubeSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = new YouTubeSite();
    }

    /**
     * @dataProvider validUrlProvider
     */
    public function testExtractsVideoIdFromValidUrls(string $url, string $expectedId): void
    {
        $this->assertSame($expectedId, $this->site->extractVideoId($url));
    }

    /**
     * @dataProvider validUrlProvider
     */
    public function testAutoEmbedCapturesAndReplacesFullUrls(string $url, string $expectedId): void
    {
        $message = "Video: " . $url . " en el cuerpo.";
        $this->site->auto($message);

        $this->assertStringNotContainsString($url, $message);
        $this->assertStringContainsString('class="oharaEmbed youtube"', $message);
       
        $this->assertStringContainsString('data-ohara_video_id="' . $expectedId . '"', $message);
    }

    public function testReturnsEmptyStringForInvalidUrls(): void
    {
        $this->assertSame('', $this->site->extractVideoId('https://youtube.com/invalid/structure'));
        $this->assertSame('', $this->site->extractVideoId('https://example.com/watch?v=MBdfBTXWtFo'));
    }

    public static function validUrlProvider(): array
    {
        return [
            'Standard Watch URL'   => ['https://www.youtube.com/watch?v=MBdfBTXWtFo', 'MBdfBTXWtFo'],
            'Short URL (youtu.be)' => ['https://youtu.be/MBdfBTXWtFo', 'MBdfBTXWtFo'],
            'Mobile URL'           => ['https://m.youtube.com/watch?v=MBdfBTXWtFo', 'MBdfBTXWtFo'],
            'Embed URL'            => ['https://www.youtube.com/embed/MBdfBTXWtFo', 'MBdfBTXWtFo'],
            'No-Cookie URL'        => ['https://www.youtube-nocookie.com/embed/MBdfBTXWtFo', 'MBdfBTXWtFo'],
            'URL with params'      => ['https://www.youtube.com/watch?v=MBdfBTXWtFo&feature=shared', 'MBdfBTXWtFo'],
        ];
    }
}