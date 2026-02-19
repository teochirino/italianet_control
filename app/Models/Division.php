<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'color',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class)->orderBy('order');
    }

    public function updateColor(): void
    {
        $stations = $this->stations()->where('active', true)->get();
        
        if ($stations->isEmpty()) {
            $this->color = 'verde';
            $this->save();
            return;
        }

        $hasRed = $stations->contains('color', 'rojo');
        $hasYellow = $stations->contains('color', 'amarillo');
        $hasGray = $stations->contains('color', 'gris');

        if ($hasRed) {
            $this->color = 'rojo';
        } elseif ($hasYellow) {
            $this->color = 'amarillo';
        } elseif ($hasGray) {
            $this->color = 'gris';
        } else {
            $this->color = 'verde';
        }

        $this->save();
    }
}
