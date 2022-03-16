<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/3/17
 * Time: 10:14 AM
 */

namespace App\Support;
//use App\Company;


trait DatabaseConnection {

    public function getConnection() {
        if (session('tenant')) {
            Company::find(session('tenant'))->connect();
            return 'tenant';
        } else {
            $connection = config('admin.database.connection') ?: config('database.default');
            return $connection;
        }
    }
}



