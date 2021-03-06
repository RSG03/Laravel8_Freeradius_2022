<?php

namespace App\Vendors\Encore\Admin\Controllers;

use Encore\Admin\Controllers\MenuController as AdminMenu;
use App\Vendors\Encore\Admin\Models\MyMenu as Menu;
use App\Vendors\Encore\Admin\Models\MyRole as Role;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Controllers\ModelForm;

class MyMenuController extends AdminMenu
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
            $content->header(trans('admin.menu'));
            $content->description(trans('admin.list'));

            if (session('tenant')) {
                $content->row(function (Row $row) {
                    $row->column(12, $this->treeView()->render());
                });
            } else {
                $content->row(function (Row $row) {
                    $row->column(6, $this->treeView()->render());

                    $row->column(6, function (Column $column) {
                        $form = new \Encore\Admin\Widgets\Form();
                        $form->action(admin_base_path('auth/menu'));

                        $form->select('parent_id', trans('admin.parent_id'))->options(Menu::selectOptions());
                        $form->text('title', trans('admin.title'))->rules('required');
                        $form->icon('icon', trans('admin.icon'))->default('fa-bars')->rules('required')->help($this->iconHelp());
                        $form->text('uri', trans('admin.uri'));
                        $form->multipleSelect('roles', trans('admin.roles'))->options(Role::all()->pluck('name', 'id'));

                        $can_view = [
                            0  => 'No',
                            1 => 'Yes'
                        ];

                        $form->select('client_can_view', 'Viewable by Clients')->options($can_view);

                        $column->append((new Box(trans('admin.new'), $form))->style('success'));
                    });
                });
            }

        });
    }

    /**
     * Redirect to edit page.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('menu.edit', ['id' => $id]);
    }

    /**
     * @return \Encore\Admin\Tree
     */
    protected function treeView()
    {
        if (session('tenant')) {
            $model = \App\Menu::class;
        } else {
            $model = config('admin.database.menu_model');
        }

        return $model::tree(function ($tree) {
            $tree->disableCreate();

            if (session('tenant')) {
                $tree->query(function ($model) {
                    return $model->where('client_can_view', 1);
                });
                $tree->useSave = false;
            }

            $tree->branch(function ($branch) {

                $payload = "<i class='fa {$branch['icon']}'></i>&nbsp;<strong>{$branch['title']}</strong>";

                if (!isset($branch['children'])) {
                    if (url()->isValidUrl($branch['uri'])) {
                        $uri = $branch['uri'];
                    } else {
                        $uri = admin_base_path($branch['uri']);
                    }

                    $payload .= "&nbsp;&nbsp;&nbsp;<a href=\"$uri\" class=\"dd-nodrag\">$uri</a>";
                }
                return $payload;
            });
        });
    }

    /**
     * Edit interface.
     *
     * @param string $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header(trans('admin.menu'));
            $content->description(trans('admin.edit'));

            $content->row($this->form()->edit($id));
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Menu::form(function (Form $form) {
            $form->display('id', 'ID');

            if (session('tenant')) {
                $form->display('title', trans('admin.title'));
            } else {
                $form->select('parent_id', trans('admin.parent_id'))->options(Menu::selectOptions());
                $form->text('title', trans('admin.title'))->rules('required');

                $form->icon('icon', trans('admin.icon'))->default('fa-bars')->rules('required')->help($this->iconHelp());
                $form->text('uri', trans('admin.uri'));

            }
            $form->multipleSelect('roles', trans('admin.roles'))->options(Role::all()->pluck('name', 'id'));
            $can_view = [
                0  => 'No',
                1 => 'Yes'
            ];

            if (!session('tenant')) {
                $form->select('client_can_view', 'Viewable by Clients')->options($can_view);
            }
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));
        });
    }

    /**
     * Help message for icon field.
     *
     * @return string
     */
    protected function iconHelp()
    {
        return 'For more icons please see <a href="http://fontawesome.io/icons/" target="_blank">http://fontawesome.io/icons/</a>';
    }
}
