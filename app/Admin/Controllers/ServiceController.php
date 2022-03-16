<?php

namespace App\Admin\Controllers;

use App\Service;

use App\Support\RadiusTrait;
use App\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ServiceController extends Controller
{
    use ModelForm, RadiusTrait;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Services');
            $content->description('List');

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

            $content->header('Service');
            $content->description('Edit');

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

            $content->header('Service');
            $content->description('Create');

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
        return Admin::grid(Service::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name('Service')->sortable();
            $grid->bandwidthdown('Download (MB)')->display(function ($bandwidthup) {
                return $bandwidthup/1000000;
            })->sortable();
            $grid->bandwidthup('Upload (MB)')->display(function ($bandwidthdown) {
                return $bandwidthdown/1000000;
            })->sortable();
            $grid->priority()->sortable();

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->filter(function($filter){

                // Add a column filter
                $filter->like('name', 'Service Name');
                $filter->between('bandwidthdown', 'Bandwidth Download (byte)');
                $filter->between('bandwidthup', 'Bandwidth Upload (byte)');
                $filter->equal('priority')->select([1 => '1', 2 => '2', 3 => '3', 4 => '4']);
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        return Admin::form(Service::class, function (Form $form) use ($id) {

            $form->display('id', 'ID');
            $form->text('name', 'Name')->rules('required');
            $form->number('bandwidthdown', 'Bandwidth Download (byte)')->rules('required|numeric|min:0');
            $form->number('bandwidthup', 'Bandwidth Upload (byte)')->rules('required|numeric|min:0');
            $form->select('priority', 'Priority')->options([1 => 1, 2 => 2, 3 => 3, 4 => 4])->rules('required');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->saving(function (Form $form) use ($id) {
                // Simpan data di tabel radius
                if ($id) {
                    $this->editGroup($id, $form);
                }
            });
            $form->saved(function (Form $form) use ($id) {
                if (!$id) {
                    $id = $form->model()->id;
                    $this->addGroup($id, $form);
                }
            });

        });
    }

    public function update($id)
    {
        return $this->form($id)->update($id);
    }

    public function destroy($id)
    {
        $usages = User::where('service_id', $id)->first();
        if ($usages) {
            return response()->json([
                'status'  => false,
                'message' => 'This service is still used by some users',
            ]);   
        }

        $this->deleteGroup($id);
        if ($this->form()->destroy($id)) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }
}
