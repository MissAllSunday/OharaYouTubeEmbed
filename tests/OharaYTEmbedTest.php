<?php

declare(strict_types=1);

namespace OharaYTEmbed\Tests;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\OharaYTEmbed;
use PHPUnit\Framework\TestCase;

class OharaYTEmbedTest extends TestCase
{
    private OharaYTEmbed $app;

    protected function setUp(): void
    {
        $this->app = new OharaYTEmbed();
    }

    // -----------------------------------------------------------------------
    // getSites()
    // -----------------------------------------------------------------------

    public function testGetSitesReturnsNonEmptyArray(): void
    {
        $sites = $this->app->getSites();
        $this->assertIsArray($sites);
        $this->assertNotEmpty($sites);
    }

    public function testGetSitesContainsOnlyEmbedSiteInterface(): void
    {
        foreach ($this->app->getSites() as $site) {
            $this->assertInstanceOf(EmbedSiteInterface::class, $site);
        }
    }

    public function testGetSitesKeyedByIdentifier(): void
    {
        foreach ($this->app->getSites() as $key => $site) {
            $this->assertSame($key, $site->identifier());
        }
    }

    public function testGetSitesContainsExpectedBuiltinSites(): void
    {
        $sites = $this->app->getSites();
        $this->assertArrayHasKey('youtube', $sites);
        $this->assertArrayHasKey('vimeo',   $sites);
        $this->assertArrayHasKey('gifv',    $sites);
    }

    public function testGetSitesIsMemoised(): void
    {
        $first  = $this->app->getSites();
        $second = $this->app->getSites();
        $this->assertSame($first, $second);
    }

    // -----------------------------------------------------------------------
    // tokens()
    // -----------------------------------------------------------------------

    public function testTokensSubstitutesCurlyBracePlaceholders(): void
    {
        $result = OharaYTEmbed::tokens(
            'Hello {name}, welcome to {place}!',
            ['name' => 'World', 'place' => 'PHP'],
        );
        $this->assertSame('Hello World, welcome to PHP!', $result);
    }

    public function testTokensLeavesUnmatchedPlaceholdersIntact(): void
    {
        $result = OharaYTEmbed::tokens('Hello {unknown}', ['name' => 'World']);
        $this->assertSame('Hello {unknown}', $result);
    }

    public function testTokensWithEmptyTemplateReturnsEmpty(): void
    {
        $this->assertSame('', OharaYTEmbed::tokens('', ['key' => 'value']));
    }

    public function testTokensWithEmptyTokensArrayIsNoOp(): void
    {
        $this->assertSame('no change', OharaYTEmbed::tokens('no change', []));
    }

    // -----------------------------------------------------------------------
    // text()
    // -----------------------------------------------------------------------

    public function testTextReturnsEmptyStringForMissingKey(): void
    {
        // $txt is empty in the test environment (bootstrap sets it to []).
        $this->assertSame('', $this->app->text('non_existent_key_xyz'));
    }

    public function testTextReturnsValueWhenKeyExists(): void
    {
        $GLOBALS['txt']['OharaYTEmbed_test_key'] = 'hello';
        $this->assertSame('hello', $this->app->text('test_key'));
        unset($GLOBALS['txt']['OharaYTEmbed_test_key']);
    }
}
