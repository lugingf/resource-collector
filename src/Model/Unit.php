<?php
declare(strict_types=1);

namespace RMS\ResourceCollector\Model;

use \Illuminate\Database\Eloquent\Model;

/**
 * Class Unit
 * @package RMS\ResourceCollector\Model
 * @mixin \Illuminate\Database\Eloquent\
 */
class Unit extends Model
{
    public const TABLE_NAME = 'unit';

    const FILED_SOURCE = "source";
    const FIELD_TYPE = "type";
    const FIELD_NAME = "name";
    const FIELD_PROPERTIES = "properties";

    protected $table = self::TABLE_NAME;
    public $timestamps = false;

    protected $fillable = [self::FILED_SOURCE, self::FIELD_TYPE, self::FIELD_NAME, self::FIELD_PROPERTIES];

    public function getName(): string
    {
        return $this->{self::FIELD_NAME};
    }
}