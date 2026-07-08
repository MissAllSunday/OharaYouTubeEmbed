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
    public function getIdentifier(): string;
    public function getRegex(): string;
    public function getAutoRegex(): string;
    public function getEmbedUrl(): string;
    public function getRequestUrl(): string;
    public function getOembedUrl(): string;
    public function getDefaultThumbUrl(): string;
    public function getCustomAspectRatio(): ?string;

}