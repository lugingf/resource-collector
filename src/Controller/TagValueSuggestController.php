<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\Model\Tag;

class TagValueSuggestController
{
    private $tagName;
    private $tagValuePart;

    public function __construct(array $tagData)
    {
        $this->tagName = $tagData['tagName'];
        $this->tagValuePart = $tagData['tagValuePart'];
    }

    public function process(): array
    {
        $result = [];
        $tags = Tag::getTagValueListByNameAndValuePart($this->tagName, $this->tagValuePart);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['tagValues'][] = ['value' => $tag];
        }
        return $result;
    }
}