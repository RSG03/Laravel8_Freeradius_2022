<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'priority', 'bandwidthup', 'bandwidthdown',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
}
