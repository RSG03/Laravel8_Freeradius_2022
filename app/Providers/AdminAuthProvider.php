<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AdminAuthProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('Encore\Admin\Controllers\AuthController', 'App\Vendors\Encore\Admin\Controllers\MyAuthController');
        $this->app->bind('Encore\Admin\Controllers\UserController', 'App\Vendors\Encore\Admin\Controllers\MyUserController');
        $this->app->bind('Encore\Admin\Controllers\RoleController', 'App\Vendors\Encore\Admin\Controllers\MyRoleController');
        $this->app->bind('Encore\Admin\Controllers\MenuController', 'App\Vendors\Encore\Admin\Controllers\MyMenuController');
        $this->app->bind('Encore\Admin\Controllers\PermissionController', 'App\Vendors\Encore\Admin\Controllers\MyPermissionController');
//        $this->app->bind('Encore\Admin\Form\Field', 'App\Vendors\Encore\Admin\Models\MyForm');
//        $this->app->bind('\Encore\Admin\Admin', 'App\Vendors\Encore\Admin\MyAdmin');
    }
}