<?php

namespace App\Admin\Controllers;

use App\Company;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Vendors\Encore\Admin\Models\MyAdministrator as Administrator;


class CompanyController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Company');
            $content->description('Perusahaan pengguna backend');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Company');
            $content->description('Perusahaan pengguna backend');

            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Company');
            $content->description('Perusahaan pengguna backend');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Company::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('Name')->sortable();
            $grid->company_code('Company Code')->sortable();
            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        return Admin::form(Company::class, function (Form $form)  use ($id) {

            $form->display('id', 'ID');
            $form->text('name')->rules('required');
            // $form->text('company_code')->rules('required');

            if ($id) {
                $form->text('company_code')->rules('required|alpha_dash|'.Rule::unique('companies')->ignore($id));
                $form->display('room_prefix');
            } else {
                $form->text('company_code')->rules('required|alpha_dash|unique:companies');
                $form->text('room_prefix')->rules('required|alpha_dash|unique:companies');
            }

            $form->text('admin_username')->rules('required')->default('admin');
            $form->password('admin_password')->rules('required|confirmed');
            $form->password('admin_password_confirmation')->rules('required')
                ->default(function ($form) {
                    return $form->model()->admin_password;
                });
            $form->ignore(['admin_password_confirmation']);
            $form->text('mysql_host')->rules('required');
            $form->text('mysql_database')->rules('required');
            $form->text('mysql_username')->rules('required');
            $form->text('mysql_password')->rules('required');
            $status = [
                0  => 'Tidak aktif',
                1 => 'Aktif'
            ];

            $form->select('status', 'Status')->options($status)->default(1)->rules('required');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->saving(function(Form $form) {

                $mysql_host = $form->mysql_host;
                $mysql_database = $form->mysql_database;
                $mysql_username = $form->mysql_username;
                $mysql_password = $form->mysql_password;

                if ($form->admin_password && $form->model()->admin_password != $form->admin_password) {
                    $form->admin_password = bcrypt($form->admin_password);
                    DB::purge('tenant_dynamic');
                    Config::set("database.connections.tenant_dynamic", [
                        "driver" => 'mysql',
                        "host" => $mysql_host,
                        "database" => $mysql_database,
                        "username" => $mysql_username,
                        "password" => $mysql_password
                    ]);

                    // Administrator::on('tenant_dynamic')->where('username', $form->admin_username)->update(['password' => $form->admin_password]);
                    DB::connection('tenant_dynamic')->table(config('admin.database.users_table'))
                        ->where('id', 1)
                        ->update([
                            'password' => $form->admin_password,
                            'username' => $form->admin_username,
                            'company_code' => $form->company_code
                        ]);

                }

            });
        });
    }

    public function update($id)
    {
        return $this->form($id)->update($id);
    }

    public function store()
    {
        return $this->form()->store();
    }
}
