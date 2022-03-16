<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/13/17
 * Time: 9:52 AM
 */

namespace App\Model\Radius;

use Illuminate\Database\Eloquent\Model;

class Radcheck extends Model
{
    protected $connection = 'radius';
    protected $table = 'radcheck';
    public $timestamps = false;

}