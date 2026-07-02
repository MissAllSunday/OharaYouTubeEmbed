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
     * Purge all registered provider buttons from the SMF editor toolbar in a single pass.
     * Works natively with SMF's two-dimensional row/button array structure.
     *
     * @param array $tags The global SMF bbc_tags array passed by reference.
     * @param array<EmbedSiteInterface> $sites Array of discoverable video sites.
     */
    public function disableVanillaTags(array $sites): void
    {
        global $context;

        if (empty($sites)) {
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

        foreach ($context['bbc_tags'] as $rowIndex => $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $buttonIndex => $button) {
                if (isset($button['code'], $purgeMap[$button['code']])) {
                    unset($context['bbc_tags'][$rowIndex][$buttonIndex]);
                }
            }
        }
    }
}