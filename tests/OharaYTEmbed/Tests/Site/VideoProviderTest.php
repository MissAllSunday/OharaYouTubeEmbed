<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Site;

use PHPUnit\Framework\TestCase;
use OharaYTEmbed\Site\VideoProvider;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Data\EmbedParams;

if (!function_exists('fetch_web_data')) {
    function fetch_web_data(string $url): string|false {
        if (str_contains($url, 'oembed.example.com')) {
            return json_encode([
                'title'         => 'Mock Video Title',
                'thumbnail_url' => 'https://example.com/thumb.jpg',
                'provider_name' => 'TestProvider'
            ]);
        }
        return false;
    }
}

class VideoProviderTest extends TestCase
{
    private function createProviderStub(
        string $id = 'test',
        string $regex = '',
        string $autoRegex = '',
        string $oembedUrl = ''
    ): VideoProvider {
        return new class($id, $regex, $autoRegex, $oembedUrl) extends VideoProvider {
            public function __construct(
                private string $id,
                private string $regex,
                private string $autoRegex,
                private string $oembedUrl
            ) {
                parent::__construct();
            }

            public function getIdentifier(): string { return $this->id; }
            public function getRegex(): string { return $this->regex; }
            public function getAutoRegex(): string { return $this->autoRegex; }
            public function getEmbedUrl(): string { return 'https://example.com/embed/{video_id}'; }
            public function getRequestUrl(): string { return 'https://example.com/watch/{video_id}'; }
            public function getOembedUrl(): string { return $this->oembedUrl; }

            public function getSetting(string $settingName, $default = null): mixed
            {
                return match ($settingName) {
                    'width' => OharaYTEmbed::DEFAULT_WIDTH,
                    'height' => OharaYTEmbed::DEFAULT_HEIGHT,
                    default => $default,
                };
            }

            public function global(string $variableName): mixed { return ''; }
            public function getText(string $key): string { return 'Invalid link'; }
        };
    }

    public function testGetDisplayNameCapitalizesIdentifier(): void
    {
        $provider = $this->createProviderStub('vimeo');
        $this->assertSame('Vimeo', $provider->getDisplayName());
    }

    public function testBbcTagMatchesIdentifier(): void
    {
        $provider = $this->createProviderStub('custom_site');
        $this->assertSame('custom_site', $provider->getBbcTag());
    }

    public function testGetTemplateReturnsCorrectTemplate(): void
    {
        $provider = $this->createProviderStub();
        $template = $provider->getTemplate();

        $this->assertStringContainsString('class="oharaEmbed {id}"', $template);
        $this->assertStringContainsString('{video_id}', $template);
    }

    public function testExtractVideoIdWithValidRegex(): void
    {
        $provider = $this->createProviderStub('test', '/watch\?v=([\w-]+)/');
        $videoId = $provider->extractVideoId('https://example.com/watch?v=test123');

        $this->assertSame('test123', $videoId);
    }

    public function testContentReturnsEmbedHtmlWhenOembedUrlExists(): void
    {
        $provider = $this->createProviderStub('test', '/watch\?v=([\w-]+)/', '', 'https://oembed.example.com?url={url}');
        $result = $provider->content('https://example.com/watch?v=test123');

        $this->assertStringContainsString('oharaEmbed', $result);
        $this->assertStringContainsString('test123', $result);
        $this->assertStringContainsString('Mock Video Title', $result);
    }

    public function testContentReturnsEmbedHtmlWhenOembedUrlEmpty(): void
    {
        $provider = $this->createProviderStub('test', '/watch\?v=([\w-]+)/', '', '');
        $result = $provider->content('https://example.com/watch?v=test123');

        $this->assertStringContainsString('test123', $result);
        $this->assertStringContainsString('oharaEmbed', $result);
    }

    public function testAutoMethodWithNoMatchingUrls(): void
    {
        $provider = $this->createProviderStub('test', '', '/(https?:\/\/other\.com\/watch\/[a-zA-Z0-9]+)/');

        $message = 'Visit https://example.com/watch?v=test123 for more info';
        $originalMessage = $message;

        $provider->auto($message);

        $this->assertSame($originalMessage, $message);
    }

    public function testAutoMethodWithEmptyAutoRegex(): void
    {
        $provider = $this->createProviderStub('test', '', '');

        $message = 'Visit https://example.com/watch?v=test123 for more info';
        $originalMessage = $message;

        $provider->auto($message);

        $this->assertSame($originalMessage, $message);
    }
}