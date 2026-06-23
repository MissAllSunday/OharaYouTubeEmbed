<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Sources\OharaYTEmbed\Sites;

use OharaYTEmbed\Sites\GifvSite;
use PHPUnit\Framework\TestCase;

class GifvSiteTest extends TestCase
{
    private GifvSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = new GifvSite();
    }

    /**
     * @dataProvider validUrlProvider
     */
    public function testExtractsVideoIdFromValidUrls(string $url, string $expectedId): void
    {
        $this->assertSame($expectedId, $this->site->extractVideoId($url));
    }

    public function testAutoDoesNotProcessIfAutoRegexIsEmpty(): void
    {
        $message = "Mira: https://i.imgur.com/joGlU0z.gifv";
        $this->site->auto($message);

        // Como GifvSite no declara AUTO_REGEX, debe quedar el texto intacto
        $this->assertStringContainsString('https://i.imgur.com/joGlU0z.gifv', $message);
        $this->assertStringNotContainsString('<div class="oharaEmbed gifv"', $message);
    }

    public static function validUrlProvider(): array
    {
        return [
            'Imgur GIFV' => ['https://i.imgur.com/joGlU0z.gifv', 'joGlU0z'],
            'Imgur WEBM' => ['https://i.imgur.com/joGlU0z.webm', 'joGlU0z'],
            'HTTP URL'   => ['http://i.imgur.com/joGlU0z.gifv', 'joGlU0z'],
        ];
    }
}