# Developer Guide: Adding Custom Embedding Sites

This guide explains how to extend the framework by adding custom third-party embedding sites. Thanks to the decoupled, reflection-based architecture, the `SiteRegistry` automatically discovers and instantiates drop-in site providers at runtime. No manual edits to core registry files or main hooks are required.

---

## Technical Overview

To register a new embedding engine, you only need to create a single PHP file containing a `final` class that implements the required declarative constants and hook methods.

### 1. File Location & Naming
* **Directory:** `Sources/OharaYTEmbed/Sites/`
* **Filename:** Must match the class name exactly with a `.php` extension (e.g., `TikTokSite.php`).
* **Namespace:** `OharaYTEmbed\Sites`

### 2. Base Extension
Your class must extend the abstract class `OharaYTEmbed\Site\VideoProvider` which implements the `EmbedSiteInterface` contract.

---

## Complete Boilerplate Implementation

Here is a complete blueprint for creating a custom integration plugin, detailing constants, structural layout overrides, and asset asset lifecycle hooks:

```php
<?php

declare(strict_types=1);

namespace OharaYTEmbed\Sites;

use OharaYTEmbed\Site\VideoProvider;

/**
 * Custom Embed Site Plugin Provider.
 * * Drop this file into Sources/OharaYTEmbed/Sites/ and the SiteRegistry
 * will automatically register the BBC tags, administration settings, and parsing engines.
 */
final class MyNewSite extends VideoProvider
{
    /** * @var string Unique lowercase alphanumeric key matching the primary BBC tag [mynewsite].
     * This key is also used to compile dynamic settings keys (e.g., 'enable_mynewsite') 
     * and CSS target elements.
     */
    public const IDENTIFIER = 'mynewsite';

    /** * @var string Regular expression used to validate and isolate the clean Video ID.
     * * Requirements:
     * 1. Must drop the prefix domain paths matching before it via the \K PCRE flag.
     * This ensures the full match index ($m[0]) becomes strictly the isolated ID string.
     * 2. Must append an alternate exact match fallback anchored pattern (e.g., |^[a-zA-Z0-9]+$) 
     * at the end so bare IDs passed directly (such as [mynewsite]ID[/mynewsite]) can bypass 
     * URL parsing safely without breaking execution.
     */
    public const REGEX = '%https://mynewsite\.com/video/\K[a-zA-Z0-9]+|^[a-zA-Z0-9]{5,12}$%i';

    /** * @var string Regular expression to capture full raw URLs within a post.
     * Used exclusively by the auto-embedding scan engine to locate unlinked plain text targets.
     * Leave empty ('') if you want to disable automatic scanning and only allow explicit BBC tags.
     */
    public const AUTO_REGEX = '%https://mynewsite\.com/video/[a-zA-Z0-9]+%i';

    /** @var string The responsive iframe embed player template path (supports the '{video_id}' token) */
    public const EMBED_URL = '[https://mynewsite.com/embed/](https://mynewsite.com/embed/){video_id}';

    /** @var string Canonical web URL pointing to the platform's native watch page */
    public const REQUEST_URL = '[https://mynewsite.com/video/](https://mynewsite.com/video/){video_id}';

    /** * @var string Endpoint URL if the site utilizes an external oEmbed API json pipeline.
     * Leave empty ('') if you want to bypass remote API web requests and use strict local layout calculations.
     */
    public const OEMBED_URL = '[https://mynewsite.com/api/oembed.json?url=](https://mynewsite.com/api/oembed.json?url=){url}';

    /** @var string Base64 string or asset URI path for the 16x16 editor toolbar icon button */
    public const BUTTON_IMAGE = 'data:image/gif;base64,R0lGODlhEAARAOMMAP//////zP//AMz//8wAAMwAAID/AIBAQEBAQAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAwALAAAAAAQABEAAwRFMDlJq70468076F5YgGRAKIIgEMJAuGzrvnBs33it33iu7/zAgHBILBqPyKRyyWw6n9CodEqtWq/YrHbL7XrB4LBIFAIAOw==';

    /**
     * Custom Layout Template Definition.
     * * By default, this method is inherited from the parent VideoProvider, returning a 
     * template tailored for our shared asynchronous script (ohvideos.js):
     * '<div class="oharaEmbed {id}" title="{title}" data-ohara_{id}="{video_id}" data-ohara_thumbnail_url="{thumbnail_url}" id="oh_{id}_{video_id}" style="width: {width}px; height: {height}px;"></div>'
     * * Override this method ONLY if your custom platform cannot be controlled asynchronously 
     * via JavaScript and needs a structural raw HTML template instead (such as a native HTML5 <video> wrapper).
     * * @return string The raw HTML layout containing format tokens like {id}, {video_id}, {width}, {height}.
     */
    public function getTemplate(): string
    {
        return '<div class="oharaEmbed {id}" id="oh_{id}_{video_id}" style="width: {width}px; height: {height}px;">'
            . '<video preload="auto" autoplay="autoplay" loop="loop" muted="muted" playsinline="playsinline" style="width: 100%; height: 100%;">'
            . '<source src="[https://cdn.mynewsite.com/stream/](https://cdn.mynewsite.com/stream/){video_id}.mp4" type="video/mp4">'
            . '</video>'
            . '</div>';
    }

    /**
     * Resource Registration Hook.
     * * Invoked during the initial bootstrap loading phase of the forum. Override this method 
     * if your custom site integration requires injecting stylesheets, external JavaScript SDKs, 
     * inline scripts, or altering allowed HTML structures within SMF's parsing safety layers.
     * * @return void
     */
    public function registerAssets(): void
    {
        global $context;

        // 1. Loading a dedicated/local JavaScript file into the theme header
        loadJavaScriptFile(
            'mynewsite_player.js', 
            [
                'local' => true,         // Look inside the current theme or default theme directory
                'default_theme' => true, // Fallback to default theme if missing in custom themes
                'defer' => true,         // Append the 'defer' attribute to avoid blocking DOM rendering
                'minimize' => true       // Compress and minimize the script if SMF minimization is enabled
            ]
        );

        // 2. Loading custom site-specific CSS layout stylesheets
        loadCSSFile(
            'mynewsite_styles.css', 
            [
                'force_current' => false, // Do not force current theme if it can fallback safely
                'validate' => true,       // Run verification on asset availability
                'minimize' => true        // Minimize stylesheet overhead
            ]
        );

        // 3. Injecting raw inline JavaScript initialization blocks
        addInlineJavaScript("
            $(document).ready(function() {
                console.log('Ohara Embed Engine: Initialized assets for " . static::IDENTIFIER . "');
            });
        ", true); // Pass true to append it to the document header/footer safely

        // 4. Modifying global context configurations (e.g., allowing specific embed markup tags)
        $context['allowed_html_tags'][] = 'object';
        $context['allowed_html_tags'][] = 'embed';
    }
}