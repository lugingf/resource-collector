<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Controller;


use RMS\ResourceCollector\Model\Tag;
use Slim\Http\Request;
use Slim\Http\Response;

class TagNameSuggestController extends AbstractController
{
    private const PARAM_TAG_NAME_PART = "tagNamePart";

    public function customProcess(Request $request, Response $responce, array $args): Response
    {
        $params = $this->getParameters($request);
        $result = [];
        $tags = Tag::getTagNameListByNamePart($params[self::PARAM_TAG_NAME_PART]);
        /* @var Tag $tag*/
        foreach ($tags as $tag) {
            $result['tagNames'][] = ['name' => $tag];
        }
        return $responce->withJson($result);
    }

    function getRequiredParameters(): array
    {
        return [self::PARAM_TAG_NAME_PART];
    }


}