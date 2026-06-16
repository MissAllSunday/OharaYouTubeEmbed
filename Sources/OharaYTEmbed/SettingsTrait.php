<?php

declare(strict_types=1);

namespace OharaYTEmbed\Traits;

use OharaYTEmbed\OharaYTEmbed\OharaYTEmbed;

trait SettingsTrait
{
    public function getSetting(string $settingName, $fallBack = false): mixed
    {
        $modSettings = $this->global('modSettings');

        if ($settingName === '' || $settingName === '0') {
            return $fallBack;
        }

        return $this->isEnable($settingName) ? $modSettings[OharaYTEmbed::PATTERN . $settingName] : $fallBack;
    }

    public function isEnable(string $settingName): bool
    {
        $modSettings = $this->global('modSettings');

        return !empty($modSettings[OharaYTEmbed::PATTERN . $settingName]);
    }

    public function modSetting(string $settingName, $fallBack = false): mixed
    {
        $modSettings = $this->global('modSettings');

        if ($settingName === '' || $settingName === '0') {
            return $fallBack;
        }

        return empty($modSettings[$settingName]) ? $fallBack : $modSettings[$settingName];
    }

    public function global(string $variableName): mixed
    {
        return $GLOBALS[$variableName] ?? false;
    }

    public function setGlobal($globalName, $globalValue): void
    {
        $GLOBALS[$globalName] = $globalValue;
    }

    /**
     * Merge scalar context keys into $context without overwriting unrelated keys.
     */
    public function setContextVars(array $contextVars): void
    {
        $context = $this->global('context');
        $context = array_merge($context, $contextVars);
        $this->setGlobal('context', $context);
    }

    public function requireOnce(string $fileName, string $dir = ''): void
    {
        if ($fileName === '' || $fileName === '0') {
            return;
        }

        require_once($this->global('sourcedir') . '/' . $fileName . '.php');
    }

    public function setTemplate(string $templateName): void
    {
        loadtemplate($templateName);
    }
}