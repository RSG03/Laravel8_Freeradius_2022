<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMysqlDataToCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('mysql_host');
            $table->string('mysql_database');
            $table->string('mysql_username');
            $table->string('mysql_password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('mysql_host');
            $table->dropColumn('mysql_database');
            $table->dropColumn('mysql_username');
            $table->dropColumn('mysql_password');
        });
    }
}
