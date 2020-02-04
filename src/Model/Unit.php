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

    protected $table = self::TABLE_NAME;

    protected $guarded = ['id'];

    protected $fillable = ["source", "type", "name", "properties"];
}