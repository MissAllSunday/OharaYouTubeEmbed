<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Services;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\Services\BbcPurgeService;
use PHPUnit\Framework\TestCase;

class BbcPurgeServiceTest extends TestCase
{
    private BbcPurgeService $service;

    protected function setUp(): void
    {
        $this->service = new BbcPurgeService();
    }

    public function testDisableVanillaCodesWithEmptyCodes(): void
    {
        $codes = [];
        $sites = [
            $this->createMock(EmbedSiteInterface::class),
        ];

        $this->service->disableVanillaCodes($codes, $sites);

        $this->assertEmpty($codes);
    }

    public function testDisableVanillaCodesWithEmptySites(): void
    {
        $codes = [
            ['tag' => 'youtube'],
        ];
        $sites = [];

        $this->service->disableVanillaCodes($codes, $sites);

        $this->assertCount(1, $codes);
    }

    public function testDisableVanillaCodesRemovesMatchingTags(): void
    {
        $site1 = $this->createMock(EmbedSiteInterface::class);
        $site1->method('getBbcTag')->willReturn('youtube');
        $site1->method('getExtraBbcTag')->willReturn('yt');

        $site2 = $this->createMock(EmbedSiteInterface::class);
        $site2->method('getBbcTag')->willReturn('vimeo');
        $site2->method('getExtraBbcTag')->willReturn(null);

        $codes = [
            ['tag' => 'youtube', 'type' => 'unparsed'],
            ['tag' => 'yt', 'type' => 'unparsed'],
            ['tag' => 'vimeo', 'type' => 'unparsed'],
            ['tag' => 'other', 'type' => 'unparsed'],
        ];

        $this->service->disableVanillaCodes($codes, [$site1, $site2]);

        $this->assertCount(1, $codes);
        $this->assertArrayHasKey(3, $codes);
        $this->assertEquals('other', $codes[3]['tag']);
    }

    public function testDisableVanillaTagsWithEmptySites(): void
    {
        $context = ['bbc_tags' => []];
        $GLOBALS['context'] = $context;

        $sites = [];

        $this->service->disableVanillaTags($sites);

        $this->assertEmpty($GLOBALS['context']['bbc_tags']);
    }

    public function testDisableVanillaTagsRemovesMatchingButtons(): void
    {
        $site1 = $this->createMock(EmbedSiteInterface::class);
        $site1->method('getBbcTag')->willReturn('youtube');
        $site1->method('getExtraBbcTag')->willReturn('yt');

        $context = [
            'bbc_tags' => [
                0 => [
                    ['code' => 'youtube', 'text' => 'YouTube'],
                    ['code' => 'yt', 'text' => 'YT'],
                ],
                1 => [
                    ['code' => 'vimeo', 'text' => 'Vimeo'],
                ],
            ],
        ];
        $GLOBALS['context'] = $context;

        $this->service->disableVanillaTags([$site1]);

        $this->assertCount(1, $GLOBALS['context']['bbc_tags']);
        $this->assertArrayHasKey(1, $GLOBALS['context']['bbc_tags']);
        $this->assertEquals('vimeo', $GLOBALS['context']['bbc_tags'][1][0]['code']);
    }

    public function testDisableVanillaTagsHandlesNonArrayRows(): void
    {
        $site = $this->createMock(EmbedSiteInterface::class);
        $site->method('getBbcTag')->willReturn('test');
        $site->method('getExtraBbcTag')->willReturn(null);

        $context = [
            'bbc_tags' => [
                0 => 'not an array',
                1 => [
                    ['code' => 'test', 'text' => 'Test'],
                ],
            ],
        ];
        $GLOBALS['context'] = $context;

        $this->service->disableVanillaTags([$site]);

        $this->assertCount(1, $GLOBALS['context']['bbc_tags']);
        $this->assertEquals('not an array', $GLOBALS['context']['bbc_tags'][0]);
    }
}
