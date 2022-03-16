<?php

namespace App\Http\Controllers;

use App\Router;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientDashboard
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function title()
    {
        return view('client.dashboard');
    }

    public static function info_box()
    {
        $data = array();

        $usages = ClientDashboard::usages();
        $users_online = ClientDashboard::users_online();
        $session_time = ClientDashboard::session_time();
        $data['upload'] = number_format($usages->upload_total, 2, '.', ',');
        $data['download'] = number_format($usages->download_total, 2, '.', ',');
        $data['users_online'] = $users_online->users_online;
        $data['avg_session_time'] = Carbon::now()->addSeconds($session_time->time_avg)->diffForHumans(null, true);

        return view('client.info_box', $data);
    }

    private static function usages() {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        $query = DB::connection('radius')->table('radacct');
        $query->select(DB::raw('COALESCE(sum(acctinputoctets)/1073741824, 0) as upload_total, COALESCE(sum(acctoutputoctets)/1073741824, 0) as download_total'));
        $query->where('acctstarttime', '>=', $start);
        $query->where('acctstarttime', '<=', $end);

        if (session('tenant')) {

            $ip = Router::where('company_id', session('tenant'))->pluck('ip_address')->toArray();
            $query->whereIn('nasipaddress', $ip);

        } else {
            $ip = Router::all()->pluck('ip_address')->toArray();
            $query->whereIn('nasipaddress', $ip);
        }

        return $query->first();
    }

    private static function users_online() {
        $query = DB::connection('radius')->table('radacct');
        $query->select(DB::raw('COALESCE(count(distinct(username)), 0) as users_online'));

        if (session('tenant')) {

            $ip = Router::where('company_id', session('tenant'))->pluck('ip_address')->toArray();
            $query->whereIn('nasipaddress', $ip);

        } else {
            $ip = Router::all()->pluck('ip_address')->toArray();
            $query->whereIn('nasipaddress', $ip);
        }
        $query->whereNull('acctstoptime');
        $query->where('acctsessiontime', '=', 0);

        return $query->first();
    }

    private static function session_time() {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        $query = DB::connection('radius')->table('radacct');
        $query->select(DB::raw('COALESCE(AVG(acctsessiontime), 0) AS time_avg'));
        $query->where('acctstarttime', '>=', $start);
        $query->where('acctstarttime', '<=', $end);

        if (session('tenant')) {

            $ip = Router::where('company_id', session('tenant'))->pluck('ip_address')->toArray();
            $query->whereIn('nasipaddress', $ip);

        } else {
            $ip = Router::all()->pluck('ip_address')->toArray();
            $query->whereIn('nasipaddress', $ip);
        }

        return $query->first();
    }
}
