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

    public string $name   = self::NAME;
    public int    $width  = 480;
    public int    $height = 270;

    public string $sourceDir;
    public string $scriptUrl;
    public string $boardDir;
    public string $boardUrl;

    private SiteRegistry $registry;

    public function __construct(?SiteRegistry $registry = null)
    {
        $this->sourceDir = $this->global('sourcedir');
        $this->scriptUrl = $this->global('scripturl');
        $this->boardDir  = $this->global('boarddir');
        $this->boardUrl  = $this->global('boardurl');

        $this->width  = (int) $this->getSetting('width',  480);
        $this->height = (int) $this->getSetting('height', 270);

        $this->registry = $registry ?? new SiteRegistry(
            $this,
            $this->sourceDir . '/' . self::NAME . '/Sites',
        );

        $this->addCss();
    }

    /** @return array<string, EmbedSiteInterface>
     * @throws ReflectionException
     */
    public function getSites(): array
    {
        return $this->registry->all();
    }

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
                self::NAME . '_enable_' . $site->identifier(),
                'label' => self::tokens($this->getText('enable_generic'), ['site' => $site->displayName()]),
            ];
        }

        $config_vars[] = '';
    }

    public function addCode(array &$codes): void
    {
        if (!$this->isEnable('enable')) {
            return;
        }

        foreach ($this->getSites() as $site) {
            if (!$this->isEnable('enable_' . $site->identifier())) {
                continue;
            }

            $codes[] = $this->buildBbcEntry($site, $site->bbcTag());

            if ($site->extraBbcTag() !== null) {
                $codes[] = $this->buildBbcEntry($site, $site->extraBbcTag());
            }
        }
    }

    public function addButtons(array &$dummy): void
    {
        global $context;

        if (!$this->isEnable('enable')) {
            return;
        }

        $buttons = [];

        foreach ($this->getSites() as $site) {
            if (!$this->isEnable('enable_' . $site->identifier())) {
                continue;
            }

            $buttons[] = [
                'code'        => $site->bbcTag(),
                'description' => self::tokens($this->getText('desc_generic'), ['site' => $site->displayName()]),
                'before'      => '[' . $site->bbcTag() . ']',
                'after'       => '[/' . $site->bbcTag() . ']',
                'image'       => $site->buttonImage(),
            ];
        }

        if ($buttons !== []) {
            $last = count($context['bbc_tags']) - 1;
            $context['bbc_tags'][$last] = array_merge($context['bbc_tags'][$last], $buttons);
        }
    }

    public function addEmbed(string &$message, mixed &$smileys, mixed &$cache_id, mixed &$parse_tags): void
    {
        global $context;

        if (!$this->isEnable('enable') || !$this->isEnable('autoEmbed') || !empty($context['ohara_disable'])) {
            return;
        }

        foreach ($this->getSites() as $site) {
            if ($this->isEnable('enable_' . $site->identifier())) {
                $site->auto($message);
            }
        }
    }

    public function addCss(): void
    {
        global $context;

        loadCSSFile('oharaEmbed.css', ['force_current' => false, 'validate' => true, 'minimize' => true]);
        loadJavaScriptFile('ohvideos.min.js', ['local' => true, 'force_current' => false, 'defer' => true, 'minimize' => false]);

        addInlineJavaScript(sprintf(
            "\n\tvar _ohWidth = %d;\n\tvar _ohHeight = %d;\n\tvar _ohSites = [];",
            $this->width,
            $this->height,
        ));

        foreach ($this->getSites() as $site) {
            if (!$this->isEnable('enable_' . $site->identifier())) {
                continue;
            }

            if ($site->jsFile() !== null && $site->jsFile() !== '') {
                loadJavaScriptFile($site->jsFile(), ['local' => true, 'default_theme' => true, 'defer' => true, 'minimize' => true]);
            }

            if ($site->cssFile() !== null && $site->cssFile() !== '') {
                loadCSSFile($site->cssFile(), ['force_current' => false, 'validate' => true, 'minimize' => true]);
            }

            if ($site->jsInline() !== null) {
                addInlineJavaScript($site->jsInline());
            }

            if ($site->allowedHtmlTag() !== null) {
                $context['allowed_html_tags'][] = $site->allowedHtmlTag();
            }
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
