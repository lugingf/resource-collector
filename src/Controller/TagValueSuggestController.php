<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;

use RMS\ResourceCollector\Model\Tag;
use Slim\Http\Request;
use Slim\Http\Response;

class TagValueSuggestController extends AbstractController
{
    private const PARAM_TAG_VALUE_PART = "tagValuePart";
    private const PARAM_TAG_NAME = "tagName";

    public function customProcess(Request $request, Response $responce, array $args): Response
    {
        $params = $this->getParameters($request);
        $result = [];
        $tags = Tag::getTagValueListByNameAndValuePart($params[self::PARAM_TAG_NAME], $params[self::PARAM_TAG_VALUE_PART]);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['tagValues'][] = ['value' => $tag];
        }
        return $responce->withJson($result);
    }

    function getRequiredParameters(): array
    {
        return [self::PARAM_TAG_NAME, self::PARAM_TAG_VALUE_PART];
    }
}