<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Sources\OharaYTEmbed\Sites;

use OharaYTEmbed\Sites\VimeoSite;
use PHPUnit\Framework\TestCase;

class VimeoSiteTest extends TestCase
{
    private VimeoSite $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->site = new VimeoSite();
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
        $message = "Vimeo enlace: " . $url;
        $this->site->auto($message);

        $this->assertStringNotContainsString($url, $message);
        $this->assertStringContainsString('class="oharaEmbed vimeo"', $message);
        $this->assertStringContainsString('data-ohara_vimeo="' . $expectedId . '"', $message);
    }

    public static function validUrlProvider(): array
    {
        return [
            'Standard Vimeo'      => ['https://vimeo.com/76979871', '76979871'],
            'Vimeo Channels'      => ['https://vimeo.com/channels/staffpicks/76979871', '76979871'],
            'Vimeo On Demand'     => ['https://vimeo.com/ondemand/pages/76979871', '76979871'],
            'Vimeo Player Domain' => ['https://player.vimeo.com/video/76979871', '76979871'],
            'With parameters'     => ['https://vimeo.com/76979871?h=foo', '76979871'],
        ];
    }
}