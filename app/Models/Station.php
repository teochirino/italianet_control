<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
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

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class)->orderBy('order');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_station_assignments')
            ->withTimestamps();
    }

    public function updateColor(): void
    {
        $attributes = $this->attributes()->where('active', true)->get();
        
        $previousColor = $this->color;
        
        if ($attributes->isEmpty()) {
            $this->color = 'verde';
        } else {
            $hasRed = $attributes->contains('color', 'rojo');
            $hasYellow = $attributes->contains('color', 'amarillo');
            $hasProgramGray = $attributes->where('name', 'PROGRAMA')->contains('color', 'gris');

            if ($hasProgramGray) {
                $this->color = 'gris';
            } elseif ($hasRed) {
                $this->color = 'rojo';
            } elseif ($hasYellow) {
                $this->color = 'amarillo';
            } else {
                $allGreen = $attributes->every(fn($attr) => $attr->color === 'verde');
                $this->color = $allGreen ? 'verde' : 'gris';
            }
        }

        if ($previousColor !== $this->color) {
            $this->color_changed_at = now();
        }

        $this->save();
        $this->division->updateColor();
    }
}
