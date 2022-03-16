<?php

namespace App\Admin\Controllers;

use App\Company;
use App\Router;

use App\RouterDevice;
use App\User;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use PEAR2\Net\RouterOS;

class RouterClientController extends Controller
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

            $content->header('Routers');
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
        return Admin::grid(Router::class, function (Grid $grid) {
            $grid->model()->where('company_id', session('tenant'));

            $grid->name('Router')->sortable();
            $grid->ip_address('IP Address');
            $grid->filter(function($filter){

                // Add a column filter
                $filter->like('name', 'Router Name');
                $filter->like('ip_address', 'IP Address');

            });

            $grid->disableRowSelector();
            $grid->disableCreation();
            $grid->disableActions();
            $grid->disableExport();
        });
    }

}
