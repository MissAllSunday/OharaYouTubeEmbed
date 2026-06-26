<?php

declare(strict_types=1);

namespace OharaYTEmbed;

use OharaYTEmbed\Contracts\EmbedSiteInterface;
use OharaYTEmbed\Site\SiteRegistry;
use OharaYTEmbed\Traits\SettingsTrait;
use ReflectionException;

/**
 * Supported sites are discovered automatically by SiteRegistry: drop a file
 * that implements EmbedSiteInterface into Sources/OharaYTEmbed/Sites/ and it
 * will appear in settings, BBC codes, and auto-embed without any other change.
 */
class OharaYTEmbed
{
    use SettingsTrait;

    public const PATTERN = 'OharaYTEmbed_';
    public const NAME    = 'OharaYTEmbed';
    public const DEFAULT_WIDTH = 480;
    public const DEFAULT_HEIGHT = 270;

    private SiteRegistry $registry;

    /**
     * @throws ReflectionException
     */
    public function __construct(?SiteRegistry $registry = null)
    {
        $this->registry = $registry ?? new SiteRegistry(
            $this->global('sourcedir') . '/' . self::NAME . '/Sites',
        );

        $this->addAssets();
    }

    /**
     * @return array<string, EmbedSiteInterface>
     * @throws ReflectionException
     */
    public function getSites(): array
    {
        return $this->registry->all();
    }

    /**
     * @throws ReflectionException
     */
    public function addSettings(array &$config_vars): void
    {
        $config_vars[] = $this->getText('title');
        $config_vars[] = ['check', self::NAME . '_enable',    'subtext' => $this->getText('enable_sub')];
        $config_vars[] = ['check', self::NAME . '_autoEmbed', 'subtext' => $this->getText('autoEmbed_sub')];
        $config_vars[] = ['int',   self::NAME . '_width',     'subtext' => $this->getText('width_sub'),  'size' => 3];
        $config_vars[] = ['int',   self::NAME . '_height',    'subtext' => $this->getText('height_sub'), 'size' => 3];

        foreach ($this->getSites() as $site) {
            $config_vars[] = [
                'check',
                self::NAME . '_enable_' . $site->getIdentifier(),
                'label' => self::tokens($this->getText('enable_generic'), ['site' => $site->getDisplayName()]),
            ];
        }

        $config_vars[] = '';
    }

    /**
     * @throws ReflectionException
     */
    public function addCode(array &$codes): void
    {
        if (!$this->isEnable('enable')) {
            return;
        }

        foreach ($this->getSites() as $site) {
            if (!$this->isEnable('enable_' . $site->getIdentifier())) {
                continue;
            }

            $codes[] = $this->buildBbcEntry($site, $site->getBbcTag());

            $extraBbcTag = $site->getExtraBbcTag();

            if ($extraBbcTag !== null) {
                $codes[] = $this->buildBbcEntry($site, $extraBbcTag);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public function addButtons(array &$dummy): void
    {
        global $context;

        if (!$this->isEnable('enable')) {
            return;
        }

        $buttons = [];

        foreach ($this->getSites() as $site) {
            if (!$this->isEnable('enable_' . $site->getIdentifier())) {
                continue;
            }

            $site->disableVanillaTag();

            $buttons[] = [
                'code'        => $site->getBbcTag(),
                'description' => self::tokens($this->getText('desc_generic'), ['site' => $site->getDisplayName()]),
                'before'      => '[' . $site->getBbcTag() . ']',
                'after'       => '[/' . $site->getBbcTag() . ']',
                'image'       => $site->getButtonImage(),
            ];
        }

        if ($buttons !== []) {
            $last = count($context['bbc_tags']) - 1;
            $context['bbc_tags'][$last] = array_merge($context['bbc_tags'][$last], $buttons);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function addEmbed(string &$message, mixed &$smileys, mixed &$cache_id, mixed &$parse_tags): void
    {
        global $context;

        if (!$this->isEnable('enable') ||
            !$this->isEnable('autoEmbed') ||
            !empty($context['ohara_disable'])) {
            return;
        }

        foreach ($this->getSites() as $site) {
            if ($this->isEnable('enable_' . $site->getIdentifier())) {
                $site->auto($message);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    public function addAssets(): void
    {
        global $context;

        loadCSSFile(OharaYTEmbed::NAME . '.css', ['force_current' => false, 'validate' => true, 'minimize' => true]);
        loadJavaScriptFile(OharaYTEmbed::NAME . '.js', ['local' => true, 'force_current' => false, 'defer' => true, 'minimize' => false]);

        addInlineJavaScript($this->tokens(
            "\n\t\tvar _ohWidth = {width};\n\t\tvar _ohHeight = {height};\n\t\tvar _ohSites = [];",
           [
               'width' => $this->getSetting('width', self::DEFAULT_WIDTH),
               'height' => $this->getSetting('height', self::DEFAULT_HEIGHT)
           ],
        ));

        foreach ($this->getSites() as $site) {
            if (!$this->isEnable('enable_' . $site->getIdentifier())) {
                continue;
            }

            $siteData = [
                'identifier' => $site->getIdentifier(),
                'embedUrl'   => $site::EMBED_URL,
            ];

            $jsonSite = json_encode($siteData, JSON_UNESCAPED_SLASHES);
            addInlineJavaScript("\n\t\t_ohSites.push(" . $jsonSite . ");");

            $site->registerAssets();
        }
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Build one SMF BBC code array entry for $site using $tag as the tag name.
     *
     * @return array<string, mixed>
     */
    private function buildBbcEntry(EmbedSiteInterface $site, string $tag): array
    {
        return [
            'tag'              => $tag,
            'type'             => 'unparsed_content',
            'content'          => '$1',
            'validate'         => static function (mixed &$bbcTag, mixed &$data, array $disabled) use ($site, $tag): void {
                if (!empty($disabled[$tag])) {
                    return;
                }
                $data = ($data === '' || $data === false || $data === null)
                    ? $site->invalid()
                    : $site->content(trim(strtr((string) $data, ['<br />' => ''])));
            },
            'disabled_content' => '$1',
            'block_level'      => true,
        ];
    }
}
