<?php

namespace App\Vendors\Encore\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Encore\Admin\Controllers\AuthController as AdminAuth;
use App\Company;

class MyAuthController extends AdminAuth
{

    /**
     * @param Request $request
     * company_code untuk database main adalah 'main'
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $company_code_lc = strtolower($request['company_code']);
        if ($company_code_lc == 'xenosxkt') {
            $credentials = $request->only(['username', 'password']);
            $validator = Validator::make($credentials, [
                'username' => 'required', 'password' => 'required'
            ]);
        } else {
            $credentials = $request->only(['username', 'password', 'company_code']);
            $validator = Validator::make($credentials, [
                'username' => 'required', 'password' => 'required', 'company_code' => 'required'
            ]);
        }

        if ($validator->fails()) {
            return Redirect::back()->withInput()->withErrors($validator);
        }

        // to separate admin user to each database
        if ($company_code_lc && $company_code_lc != '' && $company_code_lc != 'xenosxkt') {
            // $company = Company::find($company_code_lc);
            $company = Company::where('company_code', $company_code_lc)->first();
            if ($company) {
                Company::find($company->id)->connect();
                $request->session()->put('tenant', $company->id);
                // $request->session()->put('tenant', $company_code_lc);
            }
        }

        if (Auth::guard('admin')->attempt($credentials)) {
            admin_toastr(trans('admin.login_successful'));

            if ($company_code_lc && $company_code_lc != '' && $company_code_lc != 'xenosxkt') {
                // connect to tenant
                $company = Company::where('company_code', $company_code_lc)->first();
                Company::find($company->id)->connect();
                // Company::find($company_code_lc)->connect();

                // add session tenant
                $request->session()->put('tenant', $company->id);
                // $request->session()->put('tenant', $company_code_lc);
            } else {
                $request->session()->forget('tenant');
            }


            return redirect()->intended(config('admin.route.prefix'));
        }

        return Redirect::back()->withInput()->withErrors(['username' => $this->getFailedLoginMessage()]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout()
    {
        Auth::guard('admin')->logout();

        session()->forget('url.intented');
        session()->forget('tenant');

        return redirect(config('admin.route.prefix'));
    }

}
