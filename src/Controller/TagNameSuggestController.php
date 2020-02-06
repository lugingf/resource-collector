<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;


use RMS\ResourceCollector\Model\Tag;

class TagNameSuggestController
{
    private $tagNamePart;

    public function __construct(string $tagNamePart)
    {
        $this->tagNamePart = $tagNamePart;
    }

    public function process(): array
    {
        $result = [];
        $tags = Tag::getTagNameListByNamePart($this->tagNamePart);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['tagNames'][] = ['name' => $tag];
        }
        return $result;
    }
}