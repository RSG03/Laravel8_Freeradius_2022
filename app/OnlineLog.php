<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class OnlineLog extends Model
{
    protected $connection = 'tenant';
    protected $table = 'online_log';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'room_no',
    ];
}
