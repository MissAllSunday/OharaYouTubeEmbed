<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests\Services;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\Data\EmbedParams;
use OharaYTEmbed\OharaYTEmbed;
use OharaYTEmbed\Services\EmbedRendererService;
use OharaYTEmbed\Site\VideoProvider;
use PHPUnit\Framework\TestCase;

class EmbedRendererServiceTest extends TestCase
{
    private EmbedRendererService $service;

    protected function setUp(): void
    {
        $this->service = new EmbedRendererService();
    }

    private function createVideoProviderStub(array $methods = []): VideoProvider
    {
        return new class($methods) extends VideoProvider {
            private array $methods;

            public function __construct(array $methods = [])
            {
                $this->methods = $methods;
                parent::__construct();
            }

            public function getIdentifier(): string { return $this->callOrDefault('getIdentifier', 'test'); }
            public function getRegex(): string { return $this->callOrDefault('getRegex', '%test%'); }
            public function getAutoRegex(): string { return $this->callOrDefault('getAutoRegex', '%test%'); }
            public function getEmbedUrl(): string { return $this->callOrDefault('getEmbedUrl', 'https://example.com/embed/{video_id}'); }
            public function getRequestUrl(): string { return $this->callOrDefault('getRequestUrl', 'https://example.com/watch/{video_id}'); }
            public function getOembedUrl(): string { return $this->callOrDefault('getOembedUrl', 'https://example.com/oembed?url={url}'); }
            public function getDisplayName(): string { return $this->callOrDefault('getDisplayName', 'Test Site'); }
            public function getTemplate(): string { return $this->callOrDefault('getTemplate', '<iframe src="{embed_url}" width="{width}" height="{height}"></iframe>'); }
            public function getBbcTag(): string { return $this->callOrDefault('getBbcTag', 'test'); }
            public function getExtraBbcTag(): ?string { return $this->callOrDefault('getExtraBbcTag', null); }
            public function getButtonImage(): string { return $this->callOrDefault('getButtonImage', ''); }
            public function getDefaultThumbUrl(): string { return $this->callOrDefault('getDefaultThumbUrl', ''); }
            public function invalid(): string { return $this->callOrDefault('invalid', 'Invalid link'); }
            public function getSetting(string $settingName, mixed $fallBack = false): mixed { return $this->callOrDefault('getSetting', $fallBack, $settingName, $fallBack); }
            public function tokens(string $template, array $tokens): string { return $this->callOrDefault('tokens', $template, $template, $tokens); }

            private function callOrDefault(string $method, mixed $default, ...$methodArgs): mixed
            {
                if (isset($this->methods[$method])) {
                    return $this->methods[$method](...$methodArgs);
                }
                return $default;
            }
        };
    }

    public function testRenderWithDefaultDimensions(): void
    {
        $site = $this->createVideoProviderStub([
            'getSetting' => function ($key, $default) {
                return match ($key) {
                    EmbedParams::KEY_WIDTH => 560,
                    EmbedParams::KEY_HEIGHT => 315,
                    default => $default,
                };
            },
            'tokens' => function ($template, $params) {
                return str_replace(
                    ['{embed_url}', '{width}', '{height}'],
                    [$params['embed_url'], $params['width'], $params['height']],
                    $template
                );
            },
        ]);

        $params = EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID => 'test123',
            EmbedParams::KEY_EMBED_URL => 'https://example.com/embed/test123',
        ]);

        $result = $this->service->render($site, $params);

        $this->assertStringContainsString('width="560"', $result);
        $this->assertStringContainsString('height="315"', $result);
    }

    public function testRenderWithCustomDimensions(): void
    {
        $site = $this->createVideoProviderStub([
            'getSetting' => function ($key, $default) {
                return match ($key) {
                    EmbedParams::KEY_WIDTH => 800,
                    EmbedParams::KEY_HEIGHT => 600,
                    default => $default,
                };
            },
            'tokens' => function ($template, $params) {
                return str_replace(
                    ['{embed_url}', '{width}', '{height}'],
                    [$params['embed_url'], $params['width'], $params['height']],
                    $template
                );
            },
        ]);

        $params = EmbedParams::from([
            EmbedParams::KEY_VIDEO_ID => 'test123',
            EmbedParams::KEY_EMBED_URL => 'https://example.com/embed/test123',
        ]);

        $result = $this->service->render($site, $params);

        $this->assertStringContainsString('width="800"', $result);
        $this->assertStringContainsString('height="600"', $result);
    }
}
