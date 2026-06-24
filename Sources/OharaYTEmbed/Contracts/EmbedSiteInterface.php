<?php

declare(strict_types=1);

namespace OharaYTEmbed\Contracts;

/**
 * Contract every embed-site plugin must satisfy.
 *
 * Drop a file into
 * Sources/OharaYTEmbed/Sites/ that implements this interface (or extends
 * AbstractEmbedSite) and SiteRegistry will pick it up automatically.
 */
interface EmbedSiteInterface
{
    /**
     * Retrieve the custom HTML layout template used to render the player container.
     *
     * Internal Behavior:
     * - Returns the structural HTML block containing token placeholders (such as {id},
     *   {video_id}, {width}, {height}) which are hydrated dynamically by the DTO array.
     * - Can be overridden by child classes requiring specific layout structures (e.g., HTML5 video tags).
     *
     * @return string The raw HTML template with format tokens.
     */
    public function getTemplate(): string;

    /**
     * Retrieve the unique, lowercase alphanumeric key of the embedding site provider.
     *
     * Internal Behavior:
     * - Directly maps to the underlying static IDENTIFIER constant of the site plugin.
     * - Used to build dynamic administration variables, settings keys, and layout CSS classes.
     *
     * @return string The unique identification key.
     */
    public function getIdentifier(): string;

    /**
     * Retrieve the aesthetic, human-readable display name of the platform.
     *
     * Internal Behavior:
     * - Formats the identifier into an uppercase or title-case variant suitable for
     *   administration panels, tooltips, and dynamic error messages.
     *
     * @return string The platform display name.
     */
    public function getDisplayName(): string;

    /**
     * Retrieve the main BBCode tag name registered for this embedding provider.
     *
     * Internal Behavior:
     * - Typically returns the same value as the identifier, enabling users to invoke
     *   the player directly within post messages via a dedicated [tag] body [/tag] block.
     *
     * @return string The primary BBCode tag.
     */
    public function getBbcTag(): string;

    /**
     * Retrieve an alternate or secondary BBCode tag name supported by this provider, if any.
     *
     * Internal Behavior:
     * - Allows backward compatibility or shortcode aliases to map seamlessly to the
     *   same parsing pipeline (e.g., mapping [yt] as an alias for [youtube]).
     *
     * @return string|null The secondary BBCode tag, or null if no alias is registered.
     */
    public function getExtraBbcTag(): ?string;

    /**
     * Retrieve the visual asset icon data representing this provider in the WYSIWYG editor toolbar.
     *
     * Internal Behavior:
     * - Returns a Base64-encoded image string or a web-accessible URI path to a 16x16 icon file
     *   used to register the bbc button directly into SMF's posting container.
     *
     * @return string The image asset source path or base64 data stream.
     */
    public function getButtonImage(): string;

    /**
     * Resource Registration Hook.
     *
     * Internal Behavior:
     * - Invoked during the initial bootstrap loading phase of the forum.
     * - Allows site plugins to dynamically inject dependencies into the theme header or footer
     *   (e.g., using loadJavaScriptFile(), loadCSSFile(), or addInlineJavaScript()) and
     *   modify global context properties before parsing takes place.
     *
     * @return void
     */
    public function registerAssets(): void;

    /**
     * Convert BBC tag body $data (a URL or a bare video ID) into the embed HTML.
     *
     * Internal Process Lifecycle:
     * 1. Invokes extractVideoId($data). If it returns an empty string, the
     *    pipeline aborts and returns the original text untouched to prevent broken posts.
     * 2. If OEMBED_URL is defined, it runs a background request using SMF's
     *    fetch_web_data() helper, including native caching and hydration layers.
     * 3. If the remote request fails or returns invalid JSON, it invokes handleFailure()
     *    to gracefully compile a fallback layout showing the site's display name and clean ID.
     *
     * @param string $data The raw content inside the BBC tag (full URL or raw ID).
     * @return string The compiled safe HTML embedding block, or original text on failure.
     */
    public function content(string $data): string;

    /**
     * Scan $message for URLs matching AUTO_REGEX and replace them with embed HTML in-place.
     *
     * Internal Process Lifecycle:
     * 1. Performs a global regular expression match scan across the raw message body.
     * 2. Isolates unique valid matching URLs to prevent redundant processing.
     * 3. Forwards each target URL into the content() pipeline and swaps out the raw
     *    links for ready-to-render layout placeholders or player code on the fly.
     *
     * @param string &$message A reference to the raw post text body being processed.
     * @return void
     */
    public function auto(string &$message): void;

    /**
     * Return the "invalid link" error string for this site.
     *
     * Internal Process Lifecycle:
     * 1. Fetches the generic 'invalid_link' language string token from SMF's global $txt array.
     * 2. Injects the site's calculated getDisplayName() value into the localized template string.
     *
     * @return string The fully localized error message.
     */
    public function invalid(): string;
}