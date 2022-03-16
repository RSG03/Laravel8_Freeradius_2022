<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/2/17
 * Time: 2:56 PM
 * Override namespace Encore\Admin\Auth\Database;
 *
 */


namespace App\Vendors\Encore\Admin\Models;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Auth\Database\HasPermissions;
use App\Support\DatabaseConnection;
use App\Company;
use Illuminate\Support\Facades\Log;


/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class MyAdministrator extends Model implements AuthenticatableContract
{
    use Authenticatable, AdminBuilder, HasPermissions;
//    use Authenticatable, AdminBuilder, HasPermissions, DatabaseConnection;

    protected $fillable = ['username', 'password', 'name', 'avatar'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if (session('tenant')) {
            Company::find(session('tenant'))->connect();
            $this->setConnection('tenant');
        } else {
            // Log::debug('masuk ke myadministator else');
            $connection = config('admin.database.connection') ?: config('database.default');
            $this->setConnection($connection);
        }

       // $connection = $this->getConnection();
       // $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }
}
