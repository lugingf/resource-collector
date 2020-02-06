<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\TagRules;

use Illuminate\Database\Capsule\Manager as DB;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class Host2TagLinker
{
    public const TABLE = 'host2tag';

    private $table = "";

    private function getTable()
    {
        if ($this->table === "") {
            return self::TABLE;
        }
        return $this->table;
    }

    public function setTable(string $table)
    {
        $this->table = $table;
    }

    /** Пока линкаем по одному
     */
    public function linkHost(string $hostName, TagRule $rule, Tag $tag): void
    {
        DB::insert(
            'INSERT IGNORE INTO ' . $this->getTable() . ' (host_name, tag_id, rule_id) VALUES (?, ?, ?)',
            [
                $hostName,
                $tag->getId(),
                $rule->getId()
            ]
        );
    }

    public function getHostTags(string $hostName): array
    {
        $result = DB::select(
            'SELECT * FROM ' . $this->getTable() . ' WHERE host_name = ?',
            [$hostName]
        );
        return $result;
    }

    public function getHostLinkByTagId(string $hostName, int $tagId): ?\stdClass
    {
        $tagsData = $this->getHostTags($hostName);
        foreach ($tagsData as $tagData) {
            if ($tagData->tag_id == $tagId) {
                return $tagData;
            }
        }

        return null;
    }

    public function getHostTagByName(string $hostName, string $tagName): ?Tag
    {
        $tagsData = $this->getHostTags($hostName);
        foreach ($tagsData as $tagData) {
            /* @var Tag $tag */
            $tag = Tag::where('id', "=", $tagData->tag_id)->first();
            if ($tag->getName() === $tagName) {
                return $tag;
            }
        }

        return null;
    }

    public function getOwnerTagValue(string $vmName): ?string
    {
        $tag = $this->getHostTagByName($vmName, 'owner');
        return is_null($tag) ? null : $tag->getValue();
    }

    public function getHostLinksByTagId(int $tagId): array
    {
        $links = DB::select(
            'SELECT * FROM ' . $this->getTable() . ' WHERE tag_id = ?',
            [$tagId]
        );
        return $links;
    }

    public function replaceLink(int $oldLinkId, string $hostName, TagRule $rule, Tag $tag): void
    {
        DB::delete('DELETE FROM ' . $this->getTable() . ' WHERE id = ?', [$oldLinkId]);
        $this->linkHost($hostName, $rule, $tag);
    }

    public function linkHosts(array $hostList, TagRule $tagRule, Tag $tag): array
    {
        $skippedInstances = [];
        foreach ($hostList as $hostName) {
            $hostTag = $this->getHostTagByName($hostName, $tag->getName());
            // Если тега с таким именем нет - линкуем
            if (is_null($hostTag)) {
                $this->linkHost($hostName, $tagRule, $tag);
                continue;
            }

            $hostLinkData = $this->getHostLinkByTagId($hostName, $hostTag->getId());
            /* @var TagRule $savedRule */
            // @todo при реализации удалений правил - учесть возврат null
            $savedRule = TagRule::where('id', "=", $hostLinkData->rule_id)->first();
            $savedRulePriority = $savedRule->getPriority();
            if ($savedRulePriority >= $tagRule->getPriority()) {
                // тег с таким именем есть, но сохранен с бОльшим приоритетом, не линкаем, сообщим об этом
                $skippedInstances[] = [
                    'instanceName' => $hostName,
                    'tagName' => $hostTag->getName(),
                    'tagValue' => $hostTag->getValue(),
                    'rulePriority' => $savedRulePriority,
                    'ruleName' => $savedRule->getName(),
                    'ruleComment' => $savedRule->getComment()
                ];
                continue;
            }

            // тут, очевидно, приоритет был меньший, чем сейчас - заменяем линк
            $this->replaceLink($hostLinkData->id, $hostName, $tagRule, $tag);
        }
        return $skippedInstances;
    }
}