<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Model;

use Illuminate\Database\Eloquent\Model;

class TagRule extends Model
{
    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';
    const FIELD_TYPE = 'type';
    const FIELD_BODY = 'body';
    const FIELD_PRIORITY = 'priority';
    const FIELD_COMMENT = 'comment';

    const TABLE_NAME = 'tag_rule';
    protected $table = self::TABLE_NAME;

    public $timestamps = false;
    protected $guarded = [self::FIELD_ID];
    protected $fillable = [self::FIELD_NAME, self::FIELD_TYPE, self::FIELD_BODY, self::FIELD_PRIORITY, self::FIELD_COMMENT];

    public function getId(): ?int
    {
        return $this->{self::FIELD_ID};
    }

    public function getName(): ?string
    {
        return $this->{self::FIELD_NAME};
    }

    public function getBody(): string
    {
        return $this->{self::FIELD_BODY};
    }

    public function getComment(): string
    {
        return $this->{self::FIELD_COMMENT} ?? '';
    }

    public function getType(): string
    {
        return $this->{self::FIELD_TYPE};
    }

    public function getPriority(): int
    {
        return intval($this->{self::FIELD_PRIORITY});
    }

    public static function getRuleNameListByNamePart(string $namePart): array
    {
        $rules = [];
        $rulesData = TagRule::where(self::FIELD_NAME, "LIKE", "%$namePart%")->groupBy('name')->cursor();
        foreach ($rulesData as $rule) {
            $rules[] = $rule->{self::FIELD_NAME};
        }
        return $rules;
    }
}