<?php

namespace App\Vendors\Encore\Admin\Controllers;

use App\Company;
use Encore\Admin\Controllers\UserController as AdminUser;
use App\Vendors\Encore\Admin\Models\MyAdministrator as Administrator;
use Encore\Admin\Auth\Database\Permission;
//use Encore\Admin\Auth\Database\Role;
use App\Vendors\Encore\Admin\Models\MyRole as Role;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;


class MyUserController extends AdminUser
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
            $content->header(trans('admin.administrator'));
            $content->description(trans('admin.list'));
            $content->body($this->grid()->render());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        if ($id == 1 && session('tenant')) {
            return abort(404);
        }
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin.administrator'));
            $content->description(trans('admin.edit'));
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
            $content->header(trans('admin.administrator'));
            $content->description(trans('admin.create'));
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
        return Administrator::grid(function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->username(trans('admin.username'));
            $grid->name(trans('admin.name'));
            $grid->roles(trans('admin.roles'))->pluck('name')->label();
            $grid->created_at(trans('admin.created_at'));
            $grid->updated_at(trans('admin.updated_at'));

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($actions->getKey() == 1) {
                    if (Admin::user()->isAdministrator() && session('tenant') === null) {

                    } else {
                        $actions->disableDelete();
                        $actions->disableEdit();
                    }
                }
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
            $grid->disableRowSelector();
            $grid->filter(function($filter){

                // Add a column filter
                $filter->like('username', 'Username');

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form($id = null)
    {
        $connection = session('tenant') ? 'tenant' : 'main';

        return Administrator::form(function (Form $form) use ($id, $connection) {
            $form->display('id', 'ID');
            $form->hidden('company_code');


            if ($id) {
                $form->text('username', 'Username')->rules('required|alpha_dash|'.Rule::unique($connection.'.admin_users')->ignore($id));
            } else {
                $form->text('username', trans('admin.username'))->rules('required|alpha_dash|unique:'.$connection.'.admin_users');
            }

            $form->text('name', trans('admin.name'))->rules('required');
            $form->image('avatar', trans('admin.avatar'));
            $form->password('password', trans('admin.password'))->rules('required|confirmed');
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });

            $form->ignore(['password_confirmation']);

            $form->multipleSelect('roles', trans('admin.roles'))->options(Role::all()->pluck('name', 'id'));
//            $form->multipleSelect('permissions', trans('admin.permissions'))->options(Permission::all()->pluck('name', 'id'));

            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

            $form->saving(function (Form $form) {
                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }

                if (session('tenant')) {
                    $company_code = Company::find(session('tenant'))->company_code;
                    $form->company_code = $company_code;
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
        // dd($this->form()->store());
        return $this->form()->store();
    }
}
