<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $connection = 'main';

    protected $fillable = [
        'name', 'address', 'long', 'lat', 'ip_address', 'username', 'password', 'company_id'
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }

}
