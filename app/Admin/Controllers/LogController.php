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

class LogController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index($id)
    {
        // dd($id);

        return Admin::content(function (Content $content) use ($id) {

            $content->header('Log');
            $content->description('List');

            $content->body($this->grid($id));
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid($id)
    {
        $user = User::find($id);
        return Admin::grid(Radacct::class, function (Grid $grid) use ($user) {
            $grid->model()->where('username', '=', $user->username);
            $grid->model()->orderBy('radacctid', 'desc');
            $grid->disableActions();
            $grid->disableCreation();
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->radacctid('ID')->sortable();
            $grid->acctstarttime('Start Time')->sortable();
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

            $grid->filter(function($filter){

                // Add a column filter
                $filter->between('acctstarttime', 'Session Start')->datetime();
                $filter->like('acctterminatecause', 'Terminate Cause');
                $filter->between('acctoutputoctets', 'Download (byte)');
                $filter->between('acctinputoctets', 'Upload (byte)');
                $filter->like('nasipaddress', 'IP Address');
                $filter->like('callingstationid', 'MAC Address');

            });
        });
    }

}
