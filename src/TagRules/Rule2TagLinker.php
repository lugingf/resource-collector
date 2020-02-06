<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\TagRules;


use Illuminate\Database\Capsule\Manager as DB;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;

class Rule2TagLinker
{
    public const TABLE = 'rule2tag';

    /** Пока линкаем по одному, если надо будет - переделаем на массив */
    public function linkExclusively(TagRule $rule, Tag $tag): void
    {
        DB::delete(
            'DELETE FROM ' . self::TABLE . ' WHERE rule_id = ?',
            [$rule->getId()]
        );
        DB::insert(
            'INSERT IGNORE INTO ' . self::TABLE . ' (rule_id, tag_id) VALUES (?, ?)',
            [
                $rule->getId(),
                $tag->getId()
            ]
        );
    }

    public function getRuleTag(TagRule $rule): ?Tag
    {
        $result = DB::select(
            'SELECT * FROM ' . self::TABLE . ' WHERE rule_id = ?',
            [$rule->getId()]
        );
        // @fixme потенциально ошибка
        if (!isset($result['tag_id'])) {
            return null;
        }
        /* @var Tag $tag */
        $tag = Tag::where('id', "=", $result['tag_id']);
        return $tag;
    }
}