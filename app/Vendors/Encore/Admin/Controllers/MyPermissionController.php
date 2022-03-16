<?php

namespace App\Vendors\Encore\Admin\Controllers;

use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Controllers\PermissionController as AdminPermission;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Str;

class MyPermissionController extends AdminPermission
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
            $content->header(trans('admin.permissions'));
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
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin.permissions'));
            $content->description(trans('admin.edit'));
            $content->body($this->form()->edit($id));
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
            $content->header(trans('admin.permissions'));
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
        return Admin::grid(Permission::class, function (Grid $grid) {

            if (session('tenant')) {
                $grid->model()->where('client_can_view', '=', 1);
                $grid->disableCreation();
                $grid->disableActions();
                $grid->disableExport();
                $grid->disableFilter();

                $grid->tools(function (Grid\Tools $tools) {
                    $tools->batch(function (Grid\Tools\BatchActions $actions) {
                        $actions->disableDelete();
                    });
                });
                $grid->disableRowSelector();
            }

            if (!session('tenant')) {
                $grid->slug(trans('admin.slug'));
            }

            $grid->name(trans('admin.name'));

            if (!session('tenant')) {

                $grid->http_path(trans('admin.route'))->display(function ($path) {
                    return collect(explode("\r\n", $path))->map(function ($path) {
                        $method = $this->http_method ?: ['ANY'];

                        if (Str::contains($path, ':')) {
                            list($method, $path) = explode(':', $path);
                            $method = explode(',', $method);
                        }

                        $method = collect($method)->map(function ($name) {
                            return strtoupper($name);
                        })->map(function ($name) {
                            return "<span class='label label-primary'>{$name}</span>";
                        })->implode('&nbsp;');

                        $path = '/'.trim(config('admin.route.prefix'), '/').$path;

                        return "<div style='margin-bottom: 5px;'>$method<code>$path</code></div>";
                    })->implode('');
                });

                $grid->created_at(trans('admin.created_at'));
                $grid->updated_at(trans('admin.updated_at'));
            }

        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(Permission::class, function (Form $form) {
            $form->display('id', 'ID');

            $form->text('slug', trans('admin.slug'))->rules('required');
            $form->text('name', trans('admin.name'))->rules('required');

            $form->multipleSelect('http_method', trans('admin.http.method'))
                ->options($this->getHttpMethodsOptions())
                ->help(trans('admin.all_methods_if_empty'));
            $form->textarea('http_path', trans('admin.http.path'));
            $can_view = [
                0  => 'No',
                1 => 'Yes'
            ];

            $form->select('client_can_view', 'Viewable by Clients')->options($can_view);
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));
        });
    }

    /**
     * Get options of HTTP methods select field.
     *
     * @return array
     */
    protected function getHttpMethodsOptions()
    {
        return array_combine(Permission::$httpMethods, Permission::$httpMethods);
    }
}
