<?php

namespace App\Http\Controllers;

use App\Company;
use App\Model\Radius\Radcheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Config;

class LandingPageController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth');
    }


    public function index(Request $request)
    {
        Log::debug($request);
        $username = $request['username'];
        $password = $request['password'];
        $company_code = $request['company_code'];
        $email = $request['email'];
        $room_number = $request['room_number'];

        // identify company
        $company = Company::where('company_code', $company_code)->first();
        Log::debug($company->ip_landingpage);
        Log::debug($company->name);

        // get router data
        // $company = DB::connection('main')->table('routers')->where('company_id', $company->id)->first();

        // check email format
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // error email
            header('location: http://'.$company->ip_landingpage.'/login.html#errorEmail') or die("error");
        }
        // validation user & password
        $is_user_exist = Radcheck::where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->where('value', $password)
            ->first();

        if ($is_user_exist) {
            $is_accept = Radcheck::where('username', $username)
                ->where('attribute', 'Auth-Type')
                ->where('value', 'Accept')
                ->first();
            if ($is_accept) {
                Log::debug('is accept');
                // save this email and redirect to router landing page with login parameters
                DB::purge('tenant_dynamic');
                Config::set("database.connections.tenant_dynamic", [
                    "driver" => 'mysql',
                    "host" => $company->mysql_host,
                    "database" => $company->mysql_database,
                    "username" => $company->mysql_username,
                    "password" => $company->mysql_password
                ]);

                DB::connection('tenant_dynamic')->table('online_log')
                    ->insert([
                        'username' => $username,
                        'email' => $email,
                        'room_no' => $room_number,
                        'created_at' => date("Y-m-d H:i:s")
                    ]);
                header('location: http://'.$company->ip_landingpage.'/login?username=' . $username . '&dst=http://google.co.id') or die("error");
            } else {
                // rejected
                header('location: http://'.$company->ip_landingpage.'/login.html#errorReject') or die("error");

            }
        } else {
            // user not found
            header('location: http://'.$company->ip_landingpage.'/login.html#errorLogin') or die("error");
        }



    }
}
