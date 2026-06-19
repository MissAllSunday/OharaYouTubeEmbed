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
     * Convert BBC tag body $data (a URL or a bare video ID) into the embed HTML.
     * Returns $data unchanged when the input cannot be parsed.
     */
    public function content(string $data): string;

    /**
     * Scan $message for URLs matching autoEmbedRegex() and replace them with
     * embed HTML in-place.
     */
    public function auto(string &$message): void;

    /** Return the "invalid link" error string for this site. */
    public function invalid(): string;
}