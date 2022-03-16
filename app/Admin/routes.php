<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->resource('users', UserController::class)->middleware('tenant');
    $router->resource('rooms', RoomController::class)->middleware('tenant');
    $router->resource('services', ServiceController::class)->middleware('tenant');
    $router->resource('templates', TemplateController::class)->middleware('tenant');
    $router->get('routers_client', 'RouterClientController@index')->middleware('tenant');

    $router->resource('companies', CompanyController::class)->middleware('main');
    $router->resource('routers', RouterController::class)->middleware('main');

    $router->get('/', 'HomeController@index')->name('dashboard');
    $router->get('users/log/{userId}', 'LogController@index');
    $router->get('rooms/log/{userId}', 'LogRoomController@index');
    $router->get('report/disconnect/{userId}/{routerId}/{model}', 'RouterController@disconnectUser');
    $router->get('report/traffic_data', 'TrafficDataController@index');
    $router->get('report/online_log', 'OnlineLogController@index');
    $router->get('report/onlineuser', 'OnlineUserController@index');
    // $router->post('disconnect', 'RouterController@disconnectUser');
    $router->get('report/hotspot_usage_report', 'StatisticController@hotspotUsage');
    $router->get('report/hotspot_user_report', 'StatisticController@hotspotUser');
    $router->post('report/stats', 'StatisticController@ajax');
    $router->get('error', 'HomeController@error_page')->name('error_page');

    $router->post('rooms/set_status', 'RoomController@set_status');
    $router->post('rooms/create_random_password', 'RoomController@create_random_password');
    $router->post('users/create_random_password', 'UserController@create_random_password');

});
