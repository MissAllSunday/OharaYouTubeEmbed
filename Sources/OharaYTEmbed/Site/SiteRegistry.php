<?php

declare(strict_types=1);

namespace OharaYTEmbed\Site;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\OharaYTEmbed;
use ReflectionClass;
use ReflectionException;

/**
 * Discovers and instantiates embed-site plugins from a dedicated directory.
 *
 * Usage:
 *   $registry = new SiteRegistry($app, $sourcedir . '/OharaYTEmbed/Sites');
 *   $sites = $registry->all(); // array<string, EmbedSiteInterface>
 *
 * How to add a new site:
 *   1. Create Sources/OharaYTEmbed/Sites/MySite.php
 *   2. Declare `namespace OharaYTEmbed\Sites; final class MySite extends AbstractEmbedSite { ... }`
 *   3. Done — no other files need to change.
 *
 * Discovery rules:
 *   - Only *.php files directly inside $sitesDir are considered (no recursion).
 *   - The class must be autoloadable as $sitesNamespace . basename($file, '.php').
 *   - The class must implement EmbedSiteInterface and must not be abstract.
 */
final class SiteRegistry
{
    /** @var array<string, EmbedSiteInterface>  keyed by identifier() */
    private array $sites = [];

    public function __construct(
        private string $sitesDir,
        private string $sitesNamespace = 'OharaYTEmbed\\Sites\\',
    ) {}

    /**
     * Return all registered sites, lazily discovered on first call.
     *
     * @return array<string, EmbedSiteInterface>
     * @throws ReflectionException
     */
    public function all(): array
    {
        if ($this->sites !== []) {
            return $this->sites;
        }

        foreach (glob($this->sitesDir . '/*.php') ?: [] as $file) {
            /** @var EmbedSiteInterface|VideoProvider|null $site */
            $site = $this->loadFile($file);

            if ($site !== null) {
                $this->sites[$site->getIdentifier()] = $site;
            }
        }

        return $this->sites;
    }

    /**
     * Attempt to load a single site class from $file.
     * Returns null and silently skips if the class cannot be used.
     *
     * @param string $file
     * @return EmbedSiteInterface|VideoProvider
     * @throws ReflectionException
     */
    private function loadFile(string $file): ?EmbedSiteInterface
    {
        $fqcn = $this->sitesNamespace . pathinfo($file, PATHINFO_FILENAME);

        if (!class_exists($fqcn)) {
            return null;
        }

        $ref = new ReflectionClass($fqcn);

        if ($ref->isAbstract() || !$ref->implementsInterface(EmbedSiteInterface::class)) {
            return null;
        }

        /** @var EmbedSiteInterface $site */
        $site = $ref->newInstance();

        return $site;
    }
}