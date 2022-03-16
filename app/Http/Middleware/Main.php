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

class Main {

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
            return $next($request);
        } else {
            return redirect()->route('error_page')->with('xenos-warning', 'We could not proceed your request');
        }

    }
}