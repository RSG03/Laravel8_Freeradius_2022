<?php

namespace App;

use App\Support\TenantConnector;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string mysql_host
 * @property string mysql_database
 * @property string mysql_username
 * @property string mysql_password
 * @property string company_name
 */

class Company extends Model
{
    use TenantConnector;

    protected $connection = 'main';

    protected $fillable = [
        'name', 'company_code', 'status', 'mysql_host', 'mysql_database', 'mysql_username', 'mysql_password'
    ];

    public function connect() {
        $this->reconnect($this);
        return $this;
    }

    public function routers() {
        return $this->hasMany(Router::class);
    }
}
