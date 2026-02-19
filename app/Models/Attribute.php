<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'station_id',
        'name',
        'color',
        'color_changed_at',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
        'color_changed_at' => 'datetime',
    ];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Attribute $attribute) {
            $attribute->station->updateColor();
        });

        static::deleted(function (Attribute $attribute) {
            $attribute->station->updateColor();
        });
    }
}
