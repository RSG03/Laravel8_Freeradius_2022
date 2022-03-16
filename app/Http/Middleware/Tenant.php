<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/1/17
 * Time: 1:56 PM
 */


namespace App\Http\Middleware;

use App\Company;
use App\Support\TenantConnector;
use Closure;
use Encore\Admin\Admin;
use Illuminate\Support\Facades\Session;

class Tenant {

    use TenantConnector;

    /**
     * @var Company
     */
    protected $company;

    /**
     * Tenant constructor.
     * @param Company $company
     */
    public function __construct(Company $company) {
        $this->company = $company;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        if (($request->session()->get('tenant')) === null) {
            // abort(403, '<strong>Warning!</strong> Only client can access');
            // Session::put('error', array('title' => 'Warning', 'message' => 'message'));
            return redirect()->route('error_page')->with('xenos-warning', 'Only client can view');
            // return back()->with('warning', 'Only client can view');
            // return redirect()->action('App\Admin\Controllers\HomeController@error_page')->with('warning', 'Only client can view');
        } else {
            // Get the company object with the id stored in session
            $company = $this->company->find($request->session()->get('tenant'));

            // Connect and place the $company object in the view
            $this->reconnect($company);
            $request->session()->put('company', $company);
            return $next($request);
        }

    }
}