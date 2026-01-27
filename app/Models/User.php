<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function assignedStations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'user_station_assignments')
            ->withTimestamps();
    }

    public function visibleStations()
    {
        if ($this->is_admin) {
            return Station::query();
        }

        return $this->assignedStations();
    }

    public function visibleStationIds()
    {
        if ($this->is_admin) {
            return $this->visibleStations()->pluck('id');
        }

        return $this->assignedStations()->pluck('stations.id');
    }
}
