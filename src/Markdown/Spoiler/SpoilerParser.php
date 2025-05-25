<?php

namespace App\Markdown\Spoiler;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;


class SpoilerParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        // This regex looks for >!some text!<
        return InlineParserMatch::regex('!!(.+?)!!');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        // The symbol must not have any other characters immediately prior
        $previousChar = $cursor->peek(-1);
        if ($previousChar !== null && $previousChar !== ' ') {
            // peek() doesn't modify the cursor, so no need to restore state first
            return false;
        }

        // This seems to be a valid match
        // Advance the cursor to the end of the match
        $cursor->advanceBy($inlineContext->getFullMatchLength());



        // Grab the Spoiler handle
        [$handle] = $inlineContext->getSubMatches();


        // Create your node and append it
        $inlineContext->getContainer()->appendChild(new Spoiler($handle));

        return true;
    }
}
