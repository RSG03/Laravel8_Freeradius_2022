<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/1/17
 * Time: 1:58 PM
 */


namespace App\Support;

use App\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait TenantConnector {

    /**
     * Switch the Tenant connection to a different company.
     * @param Company $company
     * @return void
     * @throws
     */
    public function reconnect(Company $company) {
        // Erase the tenant connection, thus making Laravel get the default values all over again.
        DB::purge('tenant');

        // Make sure to use the database name we want to establish a connection.
        Config::set('database.connections.tenant.host', $company->mysql_host);
        Config::set('database.connections.tenant.database', $company->mysql_database);
        Config::set('database.connections.tenant.username', $company->mysql_username);
        Config::set('database.connections.tenant.password', $company->mysql_password);

        // Rearrange the connection data
        DB::reconnect('tenant');

        // Ping the database. This will throw an exception in case the database does not exists or the connection fails
        Schema::connection('tenant')->getConnection()->reconnect();
    }

}