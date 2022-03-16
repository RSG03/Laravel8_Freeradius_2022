<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\CheckRow;
use App\Admin\Extensions\DisconnectUser;
use App\Company;
use App\Model\Radius\Radacct;
use App\Room;
use App\Router;
use App\Support\MikrotikTrait;
use App\Support\RadiusTrait;

use App\User;
use Carbon\Carbon;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnlineUserController extends Controller
{
    use ModelForm;
    use RadiusTrait;
    use MikrotikTrait;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Online Users');
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

            $grid->model()->select(DB::raw('Distinct radacct.username, nasipaddress, acctstarttime, acctsessiontime, callingstationid, radacctid'))
                // ->join('radcheck', function ($join) {
                //     $join->on('radacct.username', '=', 'radcheck.username');
                // })
                // ->join($tenantDbName.'.users', function ($join) {
                //     $join->on('users.username', '=', 'radacct.username');
                // })

                // ->join($tenantDbName.'.routers', function ($join) {
                //     $join->on('routers.ip_address', '=', 'radacct.nasipaddress');
                // })
                ->whereIn('nasipaddress', $router)
                ->whereNull('radacct.acctstoptime')
                ->where('radacct.acctsessiontime', '=', 0)
                ->orderBy('radacctid', 'desc');
            $grid->username()->sortable();
            $grid->nasipaddress('IP Address');
            $grid->callingstationid('MAC Address');
            $grid->column('Online Time')->display(function () {
                $dateNow = Carbon::now();
                $dateStart = Carbon::parse($this->acctstarttime);
                $diff = $dateNow->diffForHumans($dateStart, true);
                return $diff;
            });

            if (session('tenant')) {
                $grid->actions(function ($actions) {
                    $actions->disableDelete();
                    $actions->disableEdit();

                    $row = $actions->row;
                    $user = User::where('username', $row['username'])->first();
                    $model = 'user';
                    if (!$user) {
                        $user = Room::where('username', $row['username'])->first();
                        $model = 'room';
                        if (!$user) {
                            $model = 0;
                        }
                    }
                    $router = Router::where('ip_address', $row['nasipaddress'])->first();
                    // Log::debug($actions->getResource());

                    // append an action.
                    if ($user) {
                        $data = array('userId' => $user->id, 'routerId' => $router->id, 'model' => $model);
                        $actions->append(new DisconnectUser($data));
                    }
                });
            } else {
                $grid->disableActions();
            }

            $grid->disableCreation();
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->filter(function($filter) use ($router_selection) {

                // Add a column filter
                $filter->like('username', 'Username');
                // $filter->equal('service_id', 'Service')->select(Service::all()->pluck('name', 'id'));

                $filter->equal('nasipaddress', 'Router Name')->select($router_selection);
                $filter->like('callingstationid', 'MAC Address');

            });
        });
    }

}
