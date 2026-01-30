<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalUser extends Model
{
    protected $connection = 'italianet_users';
    protected $table = 'users';
    
    protected $fillable = [
        'name',
        'apellidopaterno',
        'apellidomaterno',
        'email',
        'nomina',
        'status',
        'password',
    ];

    public $timestamps = false;

    public function scopeActive($query)
    {
        return $query->where('status', 1)
                    ->whereNotNull('email')
                    ->where('email', '!=', '');
    }

    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->name,
            $this->apellidopaterno,
            $this->apellidomaterno,
        ]);
        
        return implode(' ', $parts);
    }

    public function user()
    {
        return $this->setConnection('mysql')
            ->hasOne(User::class, 'main_user_id', 'id');
    }
}
