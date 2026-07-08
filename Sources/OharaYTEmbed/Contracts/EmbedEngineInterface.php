<?php

namespace OharaYTEmbed\Contracts;

interface EmbedEngineInterface
{
    public function content(string $videoIdOrUrl): string;
    public function auto(string &$message): void;
    public function extractVideoId(string $url): string;
}