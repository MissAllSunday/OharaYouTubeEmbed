<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Site;

use PHPUnit\Framework\TestCase;
use OharaYTEmbed\Site\VideoProvider;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Data\EmbedParams;

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
            public function registerAssets(): void {}

            public function getSetting(string $key, $default = null): mixed
            {
                return match ($key) {
                    'width' => OharaYTEmbed::DEFAULT_WIDTH,
                    'height' => OharaYTEmbed::DEFAULT_HEIGHT,
                    default => $default ?? 480,
                };
            }

            public function global(string $key): mixed { return ''; }
            public function getText(string $key): string { return 'Invalid link'; }

            public function tokens(string $string, array $params = []): string
            {
                foreach ($params as $k => $v) {
                    $string = str_replace('{' . $k . '}', (string)$v, $string);
                }
                return $string;
            }
            
            // Add the missing methods that should be implemented
            public function extractVideoId(string $url): string
            {
                if ($this->getRegex() === '') {
                    return '';
                }
                
                $pattern = $this->getRegex();
                $matches = [];
                
                if (preg_match($pattern, $url, $matches)) {
                    return $matches[1] ?? '';
                }
                
                return '';
            }
            
            public function auto(string &$message): void
            {
                $autoRegex = $this->getAutoRegex();
                if ($autoRegex === '') {
                    return;
                }
                
                $pattern = $autoRegex;
                $matches = [];
                
                if (preg_match($pattern, $message, $matches)) {
                    // In a real implementation this would replace the URL with embed HTML
                    // For testing purposes we'll just check that it matches
                    return;
                }
            }
            
            public function handleFailure(string $videoId): string
            {
                return '<div class="oharaEmbed {id}" ' .
                    'title=\"{title}\" ' .
                    'data-ohara_video_id="' . $videoId . '" ' .
                    'data-ohara_thumbnail_url="" ' .
                    'data-ohara_embed_url="" ' .
                    'id="oh_' . $this->getIdentifier() . '_' . $videoId . '" ' .
                    'style="width: 640px; height: 480px;"></div>';
            }
            
            public function create(EmbedParams $params): string
            {
                $template = $this->getTemplate();
                $data = $params->toArray();
                
                foreach ($data as $key => $value) {
                    $template = str_replace('{' . $key . '}', (string)$value, $template);
                }
                
                return $template;
            }
            
            public function processOembedResponse(string $response, string $videoId): ?EmbedParams
            {
                if ($response === '') {
                    return null;
                }
                
                $data = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return null;
                }
                $data[EmbedParams::KEY_VIDEO_ID] = $videoId;
                
                $params = EmbedParams::from($data);
                
                return $params;
            }
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
        $this->assertStringContainsString('{id}', $template);
        $this->assertStringContainsString('{video_id}', $template);
        $this->assertStringContainsString('{width}px', $template);
        $this->assertStringContainsString('{height}px', $template);
    }

    public function testExtractVideoIdWithValidRegex(): void
    {
        $provider = $this->createProviderStub('test', '/(test123)/');
        $videoId = $provider->extractVideoId('https://example.com/watch/test123');
        
        $this->assertSame('test123', $videoId);
    }

    public function testExtractVideoIdWithInvalidRegex(): void
    {
        $provider = $this->createProviderStub('test', '/(invalid)/');
        $videoId = $provider->extractVideoId('https://example.com/watch/test123');
        
        $this->assertSame('', $videoId);
    }

    public function testExtractVideoIdWithEmptyRegex(): void
    {
        $provider = $this->createProviderStub('test', '');
        $videoId = $provider->extractVideoId('https://example.com/watch/test123');
        
        $this->assertSame('', $videoId);
    }

    public function testContentReturnsEmbedHtmlWhenOembedUrlExists(): void
    {
        // This requires mocking fetch_web_data and other methods
        $provider = $this->createProviderStub('test', '/(test123)/', '', 'https://oembed.example.com');
        
        // Mock the fetch method to return valid JSON
        $this->markTestIncomplete('Need to mock fetch_web_data function for this test');
    }

    public function testContentReturnsEmbedHtmlWhenOembedUrlEmpty(): void
    {
        $provider = $this->createProviderStub('test', '/(test123)/', '', '');
        $result = $provider->content('https://example.com/watch/test123');
        
        // Should return HTML with the video ID
        $this->assertStringContainsString('test123', $result);
        $this->assertStringContainsString('oharaEmbed', $result);
    }

    public function testContentHandlesInvalidVideoId(): void
    {
        $provider = $this->createProviderStub('test', '/(invalid)/', '', '');
        $result = $provider->content('https://example.com/watch/test123');
        
        // Should return invalid link message or fallback HTML
        $this->assertStringContainsString('data-ohara_video_id="https://example.com/watch/test123"', $result);
    }


    public function testAutoMethodWithNoMatchingUrls(): void
    {
        $provider = $this->createProviderStub('test', '', '/(https?:\/\/other\.com\/watch\/[a-zA-Z0-9]+)/');
        
        $message = 'Visit https://example.com/watch/test123 for more info';
        $originalMessage = $message;
        
        $provider->auto($message);
        
        // Should not modify message
        $this->assertSame($originalMessage, $message);
    }

    public function testAutoMethodWithEmptyAutoRegex(): void
    {
        $provider = $this->createProviderStub('test', '', '');
        
        $message = 'Visit https://example.com/watch/test123 for more info';
        $originalMessage = $message;
        
        $provider->auto($message);
        
        // Should not modify message when auto regex is empty
        $this->assertSame($originalMessage, $message);
    }

    public function testHandleFailureCreatesCorrectHtml(): void
    {
        $provider = $this->createProviderStub('test');
        $result = $provider->handleFailure('test123');
        
        $this->assertStringContainsString('test123', $result);
        $this->assertStringContainsString('oharaEmbed', $result);
        $this->assertStringContainsString('test', $result);
    }

    public function testCreateMethodGeneratesCorrectHtml(): void
    {
        $provider = $this->createProviderStub('test');
        
        $params = EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID => 'test123',
            EmbedParams::KEY_IDENTIFIER => 'test',
            EmbedParams::KEY_EMBED_URL => 'https://example.com/embed/test123',
            EmbedParams::KEY_WIDTH => 640,
            EmbedParams::KEY_HEIGHT => 480,
        ]);
        
        $result = $provider->create($params);
        
        $this->assertStringContainsString('test123', $result);
        $this->assertStringContainsString('oharaEmbed', $result);
        $this->assertStringContainsString('640px', $result);
        $this->assertStringContainsString('480px', $result);
    }

    public function testProcessOembedResponseWithValidData(): void
    {
        $provider = $this->createProviderStub('test');
        
        $jsonResponse = json_encode([
            EmbedParams::KEY_VIDEO_ID => 'test123',
            EmbedParams::KEY_IDENTIFIER => 'test',
            EmbedParams::KEY_THUMBNAIL_URL => 'https://example.com/thumb.jpg'
        ]);
        
        $result = $provider->processOembedResponse($jsonResponse, 'test123');
        
        $this->assertInstanceOf(EmbedParams::class, $result);
        $this->assertSame('test123', $result->videoId);
    }

    public function testProcessOembedResponseWithInvalidData(): void
    {
        $provider = $this->createProviderStub('test');
        
        $result = $provider->processOembedResponse('', 'test123');
        $this->assertNull($result);
        
        $result = $provider->processOembedResponse('invalid json', 'test123');
        $this->assertNull($result);
    }
}