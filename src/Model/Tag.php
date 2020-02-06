<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Model;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    const FIELD_ID = 'id';
    const FIELD_NAME = 'name';
    const FIELD_VALUE = 'value';

    public const TABLE_NAME = 'tag';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;
    protected $guarded = [self::FIELD_ID];
    protected $fillable = [self::FIELD_NAME, self::FIELD_VALUE];

    public function getValue(): string
    {
        return $this->{self::FIELD_VALUE};
    }

    public function getName(): string
    {
        return $this->{self::FIELD_NAME};
    }

    public function getId(): int
    {
        return $this->{self::FIELD_ID};
    }

    public static function getTagNameListByNamePart(string $namePart): array
    {
        $tags = [];
        $tagData = Tag::where(self::FIELD_NAME, "LIKE", "%$namePart%")->group('name');
        foreach ($tagData as $data) {
            $tags[] = $data->{self::FIELD_NAME};
        }
        return $tags;
    }

    public static function getTagValueListByNameAndValuePart(string $name, string $valuePart): array
    {
        $tags = [];
        $tagData = Tag::where(self::FIELD_NAME, "=", $name)
            ->where(self::FIELD_VALUE, 'like', "%$valuePart%")->cursor();
        foreach ($tagData as $data) {
            $tags[] = $data->{self::FIELD_VALUE};
        }
        return $tags;
    }
}