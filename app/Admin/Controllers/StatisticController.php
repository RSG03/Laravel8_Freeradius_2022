<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/28/17
 * Time: 9:48 AM
 */

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Router;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class StatisticController extends Controller
{
    public function hotspotUsage()
    {
        Admin::js('/js/highcharts.js');
        Admin::js('/js/moment.min.js');
        Admin::js('/js/bootstrap-datetimepicker.min.js');

        if (session('tenant')) {
            $router_list = Router::where('company_id', session('tenant'))->pluck('name', 'id')->toArray();
        } else {
            $router_list = Router::all()->pluck('name', 'id')->toArray();
        }
        return Admin::content(function (Content $content) use($router_list) {
            $content->header('Hotspot Usage');
            $content->description('Stats');

            $content->body(view('statistic.hotspot_usage', compact('router_list')));
        });
    }

    public function hotspotUser()
    {
        Admin::js('/js/highcharts.js');
        Admin::js('/js/moment.min.js');
        Admin::js('/js/bootstrap-datetimepicker.min.js');

        if (session('tenant')) {
            $router_list = Router::where('company_id', session('tenant'))->pluck('name', 'id')->toArray();
        } else {
            $router_list = Router::all()->pluck('name', 'id')->toArray();
        }

        return Admin::content(function (Content $content) use($router_list) {
            $content->header('Hotspot User');
            $content->description('Stats');

            $content->body(view('statistic.hotspot_user', compact('router_list')));
        });
    }

    public function ajax()
    {
        Log::debug('get data ajax');
        $type = e(Input::get('type'));
        switch ($type) {
            case "hotspot-usage":
                $location = e(Input::get('location'));
                $from = e(Input::get('from'));
                $date = date_create($from);
                $to = e(Input::get('to'));

                if (Input::get('to') == null || Input::get('to') == "0000-00-00") {
                    $date = date_create($from);
                    $to = date_add($date, date_interval_create_from_date_string('1 days'));
                    $to = $to->format('Y-m-d');
                }

                $from = $from . ' 00:00:00';
                $to = $to . ' 23:59:59';

                $lokasi = "All Location";

                $query = DB::connection('radius')->table('radacct');
                $query->select(DB::raw('date(acctstarttime) as tgl, sum(acctinputoctets)/1073741824 as upload_total, sum(acctoutputoctets)/1073741824 as download_total'));
                $query->where('acctstarttime', '>=', $from);
                $query->where('acctstarttime', '<=', $to);

                if ($location != 0) {
                    $ip = Router::where('id', '=', $location)->first()->ip_address;
                    $lokasi = Router::where('id', '=', $location)->first()->name;
                    $query->where('nasipaddress', $ip);
                } else {
                    if (session('tenant')) {

                        $ip = Router::where('company_id', session('tenant'))->pluck('ip_address')->toArray();
                        $query->whereIn('nasipaddress', $ip);

                    } else {
                        $ip = Router::all()->pluck('ip_address')->toArray();
                        $query->whereIn('nasipaddress', $ip);
                    }

                }

                $query->groupBy(DB::raw('date(acctstarttime)'));

                $timeFrom = strtotime($from);
                $timeTo = strtotime($to);

                $period = date('d M Y ', $timeFrom);
                if ($timeFrom != $timeTo) {
                    $period .= ' - ' . date('d M Y', $timeTo);
                }

                $upload = $query->pluck('upload_total')->toArray();
                $upload_result = array();
                foreach ($upload as $item => $value) {
                    $upload_result[] = (int)$value;
                };
                $uploads = array_chunk($upload_result, 7);
                // Log::debug(count($uploads));


                $download = $query->pluck('download_total')->toArray();
                $download_result = array();
                foreach ($download as $item => $value) {
                    $download_result[] = (int)$value;
                };
                $downloads = array_chunk($download_result, 7);


                $tgl = $query->pluck('tgl')->toArray();
                $dates = array_chunk($tgl, 7);

                $jsonData = [
                    'count' => count($uploads),
                    'location' => $lokasi,
                    'period' => $period,
                    'upload' => $uploads,
                    'download' => $downloads,
                    'tgl' => $dates,
                    'total_graph' => count($uploads)
                ];

                return response()->json($jsonData);

                break;

            case "hotspot-user":
                $location = e(Input::get('location'));
                $from = e(Input::get('from'));
                $date = date_create($from);
                $to = e(Input::get('to'));

                if (Input::get('to') == null || Input::get('to') == "0000-00-00") {
                    $date = date_create($from);
                    $to = date_add($date, date_interval_create_from_date_string('1 days'));
                    $to = $to->format('Y-m-d');
                }

                $from = $from . ' 00:00:00';
                $to = $to . ' 23:59:59';

                $lokasi = "All Location";

                $query = DB::connection('radius')->table('radacct');
                $query->select(DB::raw('date(acctstarttime) as tgl, count(distinct username) as user_total'));
                $query->where('acctstarttime', '>=', $from);
                $query->where('acctstarttime', '<=', $to);

                if ($location != 0) {
                    $ip = Router::where('id', '=', $location)->first()->ip_address;
                    $lokasi = Router::where('id', '=', $location)->first()->name;
                    $query->where('nasipaddress', $ip);
                } else {
                    if (session('tenant')) {

                        $ip = Router::where('company_id', session('tenant'))->pluck('ip_address')->toArray();
                        $query->whereIn('nasipaddress', $ip);

                    } else {
                        $ip = Router::all()->pluck('ip_address')->toArray();
                        $query->whereIn('nasipaddress', $ip);
                    }

                }

                $query->groupBy(DB::raw('date(acctstarttime)'));

                $timeFrom = strtotime($from);
                $timeTo = strtotime($to);

                $period = date('d M Y ', $timeFrom);
                if ($timeFrom != $timeTo) {
                    $period .= ' - ' . date('d M Y', $timeTo);
                }

                $user = $query->pluck('user_total')->toArray();
                $user_result = array();
                foreach ($user as $item => $value) {
                    $user_result[] = (int)$value;
                };
                $users = array_chunk($user_result, 7);


                $tgl = $query->pluck('tgl')->toArray();
                $dates = array_chunk($tgl, 7);

                // Log::debug('jumlah user');
                // Log::debug(count($users));
                $jsonData = [
                    'count' => count($users),
                    'location' => $lokasi,
                    'period' => $period,
                    'user' => $users,
                    'tgl' => $dates,
                    'total_graph' => count($users)
                ];

                return response()->json($jsonData);

                break;

        }
    }

}