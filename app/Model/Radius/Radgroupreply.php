<?php

namespace App\Model\Radius;

use Illuminate\Database\Eloquent\Model;

class Radgroupreply extends Model
{
    protected $connection = 'radius';
    protected $table = 'radgroupreply';
    public $timestamps = false;

}