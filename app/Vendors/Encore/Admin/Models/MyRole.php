<?php

namespace App\Vendors\Encore\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Company;
use App\Model\ConnectionModel;


class MyRole extends Model
{
    protected $fillable = ['name', 'slug'];

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
            $connection = config('admin.database.connection') ?: config('database.default');
            $this->setConnection($connection);
        }
       // $connection = config('admin.database.connection') ?: config('database.default');
       //
       // $this->setConnection($connection);

        $this->setTable(config('admin.database.roles_table'));

        parent::__construct($attributes);
    }

    /**
     * A role belongs to many users.
     *
     * @return BelongsToMany
     */
    public function administrators() : BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.users_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'role_id', 'user_id');
    }

    /**
     * A role belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions() : BelongsToMany
    {

        $pivotTable = config('admin.database.role_permissions_table');
        $tableName = $pivotTable;
        if (session('tenant')) {
            $dbName = Company::find(session('tenant'))->mysql_database;
            $tableName = "$dbName.$pivotTable";
        }

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $tableName, 'role_id', 'permission_id');
    }

    /**
     * Check user has permission.
     *
     * @param $permission
     *
     * @return bool
     */
    public function can(string $permission) : bool
    {
        return $this->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Check user has no permission.
     *
     * @param $permission
     *
     * @return bool
     */
    public function cannot(string $permission) : bool
    {
        return !$this->can($permission);
    }
}
