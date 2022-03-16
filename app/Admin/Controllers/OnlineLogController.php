<?php

namespace App\Admin\Controllers;

use App\OnlineLog;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class OnlineLogController extends Controller
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

            $content->header('Online Log');
            $content->description('List');

            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(OnlineLog::class, function (Grid $grid) {

            $grid->username('Username / Room No.')->sortable();
            $grid->email()->sortable();
            $grid->created_at('Login Time')->sortable();

            $grid->filter(function($filter) {

                // Add a column filter
                $filter->between('created_at', 'Login Time')->datetime();
                $filter->like('username', 'Username / Room No.');
            });
            $grid->disableActions();
            $grid->disableCreation();
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });
            $grid->disableRowSelector();
        });
    }

}
