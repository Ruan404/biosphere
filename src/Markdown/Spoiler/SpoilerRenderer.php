<?php
namespace App\Markdown\Spoiler;
use ElGigi\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\CommonMarkConverter;

class SpoilerRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!($node instanceof Spoiler)) {
            throw new \InvalidArgumentException('Expected Spoiler node');
        }

        // Re-parse the inner Markdown content
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $converter->getEnvironment()->addExtension(new EmojiExtension);

        $innerHtml = $converter->convertToHtml($node->content);

        return "<details class=\"spoiler\"><summary>Spoiler</summary>{$innerHtml}</details>";
    }
}
