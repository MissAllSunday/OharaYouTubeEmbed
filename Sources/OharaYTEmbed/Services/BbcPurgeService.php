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
        $indicesToRemove = [];

        foreach ($codes as $index => $code) {
            if (isset($code['tag'], $purgeMap[$code['tag']])) {
                $indicesToRemove[] = $index;
            }
        }

        foreach (array_reverse($indicesToRemove) as $index) {
            unset($codes[$index]);
        }
    }

    /**
     * Purge all registered provider buttons from the SMF editor toolbar in a single pass.
     * Works natively with SMF's two-dimensional row/button array structure.
     *
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
        $rowsToDelete = [];

        foreach ($context['bbc_tags'] as $rowIndex => $row) {
            // Skip non-array rows – they are left untouched
            if (!is_array($row)) {
                continue;
            }

            $newRow = [];
            foreach ($row as $button) {
                // Keep buttons whose code is not being purged
                if (!isset($button['code'], $purgeMap[$button['code']])) {
                    $newRow[] = $button;
                }
            }

            if (empty($newRow)) {
                $rowsToDelete[] = $rowIndex;
            } else {
                // Replace the row with the filtered version
                $context['bbc_tags'][$rowIndex] = $newRow;
            }
        }

        // Delete empty rows
        foreach ($rowsToDelete as $rowIndex) {
            unset($context['bbc_tags'][$rowIndex]);
        }
    }
}
