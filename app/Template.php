<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name', 'description', 'cover', 'filename'
    ];

}
