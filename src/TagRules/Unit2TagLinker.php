<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\TagRules;

use Illuminate\Database\Capsule\Manager as DB;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class Unit2TagLinker
{
    public const TABLE = 'unit2tag';

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
    public function linkUnit(string $unitName, TagRule $rule, Tag $tag): void
    {
        DB::insert(
            'INSERT IGNORE INTO ' . $this->getTable() . ' (unit_name, tag_id, rule_id) VALUES (?, ?, ?)',
            [
                $unitName,
                $tag->getId(),
                $rule->getId()
            ]
        );
    }

    public function getUnitTags(string $unitName): array
    {
        $result = DB::select(
            'SELECT * FROM ' . $this->getTable() . ' WHERE unit_name = ?',
            [$unitName]
        );
        return $result;
    }

    public function getUnitLinkByTagId(string $unitName, int $tagId): ?\stdClass
    {
        $tagsData = $this->getUnitTags($unitName);
        foreach ($tagsData as $tagData) {
            if ($tagData->tag_id == $tagId) {
                return $tagData;
            }
        }

        return null;
    }

    public function getUnitTagByName(string $unitName, string $tagName): ?Tag
    {
        $tagsData = $this->getUnitTags($unitName);
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
        $tag = $this->getUnitTagByName($vmName, 'owner');
        return is_null($tag) ? null : $tag->getValue();
    }

    public function getUnitLinksByTagId(int $tagId): array
    {
        $links = DB::select(
            'SELECT * FROM ' . $this->getTable() . ' WHERE tag_id = ?',
            [$tagId]
        );
        return $links;
    }

    public function replaceLink(int $oldLinkId, string $unitName, TagRule $rule, Tag $tag): void
    {
        DB::delete('DELETE FROM ' . $this->getTable() . ' WHERE id = ?', [$oldLinkId]);
        $this->linkUnit($unitName, $rule, $tag);
    }

    public function linkUnits(array $unitList, TagRule $tagRule, Tag $tag): array
    {
        $skippedInstances = [];
        foreach ($unitList as $unitName) {
            $unitTag = $this->getUnitTagByName($unitName, $tag->getName());
            // Если тега с таким именем нет - линкуем
            if (is_null($unitTag)) {
                $this->linkUnit($unitName, $tagRule, $tag);
                continue;
            }

            $unitLinkData = $this->getUnitLinkByTagId($unitName, $unitTag->getId());
            /* @var TagRule $savedRule */
            // @todo при реализации удалений правил - учесть возврат null
            $savedRule = TagRule::where('id', "=", $unitLinkData->rule_id)->first();
            $savedRulePriority = $savedRule->getPriority();
            if ($savedRulePriority >= $tagRule->getPriority()) {
                // тег с таким именем есть, но сохранен с бОльшим приоритетом, не линкаем, сообщим об этом
                $skippedInstances[] = [
                    'instanceName' => $unitName,
                    'tagName' => $unitTag->getName(),
                    'tagValue' => $unitTag->getValue(),
                    'rulePriority' => $savedRulePriority,
                    'ruleName' => $savedRule->getName(),
                    'ruleComment' => $savedRule->getComment()
                ];
                continue;
            }

            // тут, очевидно, приоритет был меньший, чем сейчас - заменяем линк
            $this->replaceLink($unitLinkData->id, $unitName, $tagRule, $tag);
        }
        return $skippedInstances;
    }
}