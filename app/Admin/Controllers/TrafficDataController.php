<?php

namespace App\Admin\Controllers;

use App\Model\Radius\Radacct;
use App\Router;

use App\User;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Carbon\Carbon;

class TrafficDataController extends Controller
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

            $content->header('Traffic Data');
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
        if (session('tenant')) {
            $router = Router::where('company_id', session('tenant'))->pluck('ip_address');
            $router_selection = Router::where('company_id', session('tenant'))->pluck('name', 'ip_address');
        } else {
            $router = Router::all()->pluck('ip_address');
            $router_selection = Router::all()->pluck('name', 'ip_address');
        }

        return Admin::grid(Radacct::class, function (Grid $grid) use ($router, $router_selection) {
            $grid->model()->whereIn('nasipaddress', $router);
            $grid->model()->orderBy('radacctid', 'desc');

            $grid->radacctid('ID')->sortable();
            $grid->username()->sortable();
            $grid->acctstarttime('Start Time')->sortable();
            $grid->acctstoptime('Stop Time')->sortable();
            $grid->acctsessiontime('Session Time')->display(function ($acctsessiontime) {
                $dateNow = Carbon::now();

                $diff = $dateNow->addSeconds($acctsessiontime)->diffForHumans(null, true);
                return $diff;
            })->sortable();
            $grid->nasipaddress('IP Address');
            $grid->callingstationid('MAC Address');
            $grid->acctinputoctets('Upload (Mb)')->display(function ($acctinputoctets) {
                return round($acctinputoctets/1048576, 2);
            })->sortable();
            $grid->acctoutputoctets('Download (Mb)')->display(function ($acctoutputoctets) {
                return round($acctoutputoctets/1048576, 2);
            })->sortable();
            $grid->acctterminatecause('Terminate Cause');

            $grid->filter(function($filter) use ($router_selection) {

                // Add a column filter
                $filter->between('acctstarttime', 'Session Start')->datetime();
                $filter->like('acctterminatecause', 'Terminate Cause');
                $filter->between('acctoutputoctets', 'Download (byte)');
                $filter->between('acctinputoctets', 'Upload (byte)');
                $filter->equal('nasipaddress', 'Router Name')->select($router_selection);
                // $filter->like('nasipaddress', 'IP Address');
                $filter->like('callingstationid', 'MAC Address');

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
