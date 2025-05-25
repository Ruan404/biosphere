<?php

namespace App\Markdown\Spoiler;

use App\Markdown\Spoiler\Spoiler;
use App\Markdown\Spoiler\SpoilerParser;
use App\Markdown\Spoiler\SpoilerRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;

final class SpoilerExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Register your parsers, renderers, etc.
        $environment
            ->addInlineParser(new SpoilerParser, 20)
            ->addRenderer(Spoiler::class, new SpoilerRenderer, 0)
        ;
    }
}