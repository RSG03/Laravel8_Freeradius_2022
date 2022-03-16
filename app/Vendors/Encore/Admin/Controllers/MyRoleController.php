<?php

namespace App\Vendors\Encore\Admin\Controllers;

use Encore\Admin\Auth\Database\Permission;
use App\Vendors\Encore\Admin\Models\MyRole as Role;
//use App\Facades\MyAdmin as Admin;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
//use App\Vendors\Encore\Admin\MyForm as Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Encore\Admin\Controllers\RoleController as AdminRole;
use Encore\Admin\Controllers\ModelForm;
use App\Company;
use Illuminate\Support\Facades\DB;


class MyRoleController extends AdminRole
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
            $content->header(trans('admin.roles'));
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
        if ($id == 1) {
            return abort(404);
        }
        $this->permissionTenant($id);
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin.roles'));
            $content->description(trans('admin.edit'));
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Set flash session for used on listbox old value on tenant's user
     *
     * @param $id
     */
    private function permissionTenant($id) {
        if (session('tenant')) {
            Company::find(session('tenant'))->connect();
            $permission = DB::connection('tenant')->table(config('admin.database.role_permissions_table'))->select('permission_id')->where('role_id', '=', $id)->pluck('permission_id');
            session()->flash('permissions', $permission->toArray());
        }
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header(trans('admin.roles'));
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
        return Admin::grid(Role::class, function (Grid $grid) {
            $grid->id('ID')->sortable();
            $grid->name(trans('admin.name'));

            $grid->permissions(trans('admin.permission'))->pluck('name')->label();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($actions->row->slug == 'administrator') {
                    $actions->disableDelete();
                    $actions->disableEdit();
                }
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });

            $grid->filter(function($filter){

                $filter->like('name', 'Name');
                $filter->where(function ($query) {

                    $query->whereHas('permissions', function ($query) {
                        $query->where('name', 'like', "%{$this->input}%");
                    });

                }, 'Permissions');

            });

            $grid->disableRowSelector();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(Role::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('slug', trans('admin.slug'))->rules('required|alpha_dash');
            $form->text('name', trans('admin.name'))->rules('required');

            if (session('tenant')) {
                $form->listbox('permissions', trans('admin.permissions'))->options(Permission::where('client_can_view', 1)->pluck('name', 'id'));


            } else {
                $form->listbox('permissions', trans('admin.permissions'))->options(Permission::all()->pluck('name', 'id'));
            }

            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));
        });
    }
}
