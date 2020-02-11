<?php

namespace RMT\ResourceCollector;

use RMS\ResourceCollector\Model\Tag;
use Slim\Http\Response;
use Slim\Http\Request;

class TagSuggestTest extends SlimBaseTest
{
    public function setUp(): void
    {
        parent::setUp();
        $this->migrateDb();
        $this->initApp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->cleanTables();
    }

    /**
     * @dataProvider tagRequestData
     * @throws \Throwable
     */
    public function testTagSuggest(array $data)
    {
        $this->prepareTagData();
        /** @var Response $response */
        $response = $this->runApp(
            'GET',
            '/tag_suggest', ["Content-Type" => "application/json"],
            null,
            json_encode(
                ["tagNamePart" => $data["namePart"]]
            )
        );

        $this->assertEquals(200, $response->getStatusCode());

        $response->getBody()->rewind();
        $payload = json_decode($response->getBody()->getContents());

        $this->assertEquals($data["result"], $payload->tagNames[0]->name);
    }

    public function tagRequestData()
    {
        return [
            [["namePart" => "own", "result" => "owner"]],
            [["namePart" => "envi", "result" => "environment"]],
            [["namePart" => "new", "result" => "newTag"]],
        ];
    }

    private function prepareTagData()
    {
        $data = [
            ["owner", "avia"],
            ["environment", "prod"],
            ["newTag", "newVal"],
        ];
        $tagData = [];
        foreach ($data as $values) {
            $tagData[] = ["name" => $values[0], "value" => $values[1]];
        }
        Tag::insert($tagData);
    }
}
