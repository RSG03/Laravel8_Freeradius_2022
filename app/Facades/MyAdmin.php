<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class MyAdmin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Vendors\Encore\Admin\MyAdmin::class;
    }
}
