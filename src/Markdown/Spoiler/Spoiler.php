<?php
// Spoiler.php
namespace App\Markdown\Spoiler;
use League\CommonMark\Node\Inline\AbstractInline;

class Spoiler extends AbstractInline
{
    public string $content;
    public function __construct(string $content)
    {
        parent::__construct();
        $this->content = $content;
    }
}
