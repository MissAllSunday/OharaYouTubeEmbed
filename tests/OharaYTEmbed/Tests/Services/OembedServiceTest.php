<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Services;

use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\Services\OembedService;
use PHPUnit\Framework\TestCase;

class OembedServiceTest extends TestCase
{
    private OembedService $service;

    protected function setUp(): void
    {
        $this->service = new OembedService();
    }

    public function testProcessResponseWithEmptyString(): void
    {
        $result = $this->service->processResponse('', 'test123');
        $this->assertNull($result);
    }

    public function testProcessResponseWithZeroString(): void
    {
        $result = $this->service->processResponse('0', 'test123');
        $this->assertNull($result);
    }

    public function testProcessResponseWithInvalidJson(): void
    {
        $result = $this->service->processResponse('not valid json', 'test123');
        $this->assertNull($result);
    }

    public function testProcessResponseWithEmptyArray(): void
    {
        $result = $this->service->processResponse('[]', 'test123');
        $this->assertInstanceOf(EmbedParams::class, $result);
        $this->assertEquals('test123', $result->videoId);
        $this->assertEquals('', $result->title);
        $this->assertEquals('', $result->thumbnailUrl);
    }

    public function testProcessResponseWithValidJson(): void
    {
        $json = json_encode([
            'title' => 'Test Video Title',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
        ]);

        $result = $this->service->processResponse($json, 'abc123');

        $this->assertInstanceOf(EmbedParams::class, $result);
        $this->assertEquals('abc123', $result->videoId);
        $this->assertEquals('Test Video Title', $result->title);
        $this->assertEquals('https://example.com/thumb.jpg', $result->thumbnailUrl);
    }

    public function testProcessResponseWithMissingFields(): void
    {
        $json = json_encode([]);

        $result = $this->service->processResponse($json, 'xyz789');

        $this->assertInstanceOf(EmbedParams::class, $result);
        $this->assertEquals('xyz789', $result->videoId);
        $this->assertEquals('', $result->title);
        $this->assertEquals('', $result->thumbnailUrl);
    }

    public function testProcessResponseWithPartialFields(): void
    {
        $json = json_encode([
            'title' => 'Only Title Provided',
        ]);

        $result = $this->service->processResponse($json, 'def456');

        $this->assertInstanceOf(EmbedParams::class, $result);
        $this->assertEquals('def456', $result->videoId);
        $this->assertEquals('Only Title Provided', $result->title);
        $this->assertEquals('', $result->thumbnailUrl);
    }
}
