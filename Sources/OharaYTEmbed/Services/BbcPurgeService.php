<?php

declare(strict_types=1);

namespace OharaYTEmbed\Services;

use OharaYTEmbed\Contracts\EmbedSiteInterface;

class BbcPurgeService
{
    /**
     * Purge all registered provider business rules from the global BBC codes array in a single pass.
     * Handles the back-end parsing definitions.
     *
     * @param array $codes The global SMF BBC codes array passed by reference.
     * @param array<EmbedSiteInterface> $sites Array of discoverable video sites.
     */
    public function disableVanillaCodes(array &$codes, array $sites): void
    {
        if (empty($codes) || empty($sites)) {
            return;
        }

        $tagsToPurge = [];
        foreach ($sites as $site) {
            $tagsToPurge[] = $site->getBbcTag();
            if ($site->getExtraBbcTag() !== null) {
                $tagsToPurge[] = $site->getExtraBbcTag();
            }
        }

        $purgeMap = array_flip($tagsToPurge);

        foreach ($codes as $index => $code) {
            if (isset($code['tag'], $purgeMap[$code['tag']])) {
                unset($codes[$index]);
            }
        }
    }

    /**
     * Purge all registered provider tags/buttons from the global BBC tags editor array in a single pass.
     * Handles the front-end editor buttons.
     *
     * @param array $tags The global SMF BBC tags array (editor buttons) passed by reference.
     * @param array<EmbedSiteInterface> $sites Array of discoverable video sites.
     */
    public function disableVanillaTags(array &$tags, array $sites): void
    {
        if (empty($tags) || empty($sites)) {
            return;
        }

        $tagsToPurge = [];
        foreach ($sites as $site) {
            $tagsToPurge[] = $site->getBbcTag();
            if ($site->getExtraBbcTag() !== null) {
                $tagsToPurge[] = $site->getExtraBbcTag();
            }
        }

        $purgeMap = array_flip($tagsToPurge);

        foreach ($tags as $index => $tag) {
            $tagName = is_array($tag) ? ($tag['tag'] ?? $index) : $tag;

            if (isset($purgeMap[$tagName])) {
                unset($tags[$index]);
            }
        }
    }
}