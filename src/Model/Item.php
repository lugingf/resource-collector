<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Class Item
 * @package RMS\ResourceCollector\Modelч
 * @mixin \Illuminate\Database\Eloquent\
 */
class Item extends Model
{
    public const TABLE_NAME = 'item';

    const FIELD_UNIT_NAME = "unit_name";
    const FIELD_TYPE = "type";
    const FIELD_AMOUNT = "amount";
    const FIELD_PROPERTIES = "properties";

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [self::FIELD_UNIT_NAME, self::FIELD_TYPE, self::FIELD_AMOUNT, self::FIELD_PROPERTIES];
}