<?php
declare(strict_types=1);


namespace RMS\ResourceCollector\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Item
 * @package RMS\ResourceCollector\Modelч
 * @mixin \Illuminate\Database\Eloquent\
 */
class Item extends Model
{
    public const TABLE_NAME = 'item';

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = ["unit_id", "type", "amount", "properties"];
}