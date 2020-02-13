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
        return $this->attributes[self::FIELD_ID];
    }

    public function getName(): ?string
    {
        return $this->attributes[self::FIELD_NAME];
    }

    public function getBody(): string
    {
        return $this->attributes[self::FIELD_BODY];
    }

    public function getComment(): string
    {
        return $this->attributes[self::FIELD_COMMENT] ?? '';
    }

    public function getType(): string
    {
        return $this->attributes[self::FIELD_TYPE];
    }

    public function getPriority(): int
    {
        return intval($this->attributes[self::FIELD_PRIORITY]);
    }
}