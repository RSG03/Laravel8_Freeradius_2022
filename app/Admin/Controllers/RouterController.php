<?php

namespace App\Admin\Controllers;

use App\Company;
use App\Room;
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

class RouterController extends Controller
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
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('Router');
            $content->description('Edit');

            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('Router');
            $content->description('Create');

            $content->body($this->form());
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

            $grid->id('ID')->sortable();
            $grid->name('Router')->sortable();
            $grid->ip_address('IP Address');
            $grid->company()->name('Company');
            $grid->created_at();
            $grid->updated_at();
            $grid->filter(function($filter){

                // Add a column filter
                $filter->like('name', 'Router Name');
                $filter->like('ip_address', 'IP Address');

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        return Admin::form(Router::class, function (Form $form) use ($id) {

            $form->display('id', 'ID');
            $form->select('company_id', 'Company')->options(Company::all()->pluck('name', 'id'));
            $form->text('name', 'Name')->rules('required');
            $form->text('description', 'Description');
            $form->text('place', 'Location');
            $form->text('address', 'Address');
            $form->text('long', 'Long.');
            $form->text('lat', 'Lat.');

            if ($id) {
                $form->text('ip_address', 'IP Address')->rules('required|ip|'.Rule::unique('routers')->ignore($id));
            } else {
                $form->text('ip_address', 'IP Address')->rules('required|ip|unique:routers');
            }
            // $form->text('ip_address', 'IP Address')->rules('required');
            $form->text('username', 'Username')->rules('required');
            $form->text('password', 'Password')->rules('required');

        });
    }

    public function disconnectUser($userId, $routerId, $model)
    {
        // $userId = Input::get('userId');
        // $routerId = Input::get('routerId');
        $user = '';
        if ($model == 'user') {
            $user = User::find($userId);
        } elseif ($model == 'room') {
            $user = Room::find($userId);
        }

        if (!$user) {
            return false;
        }

        $router = Router::find($routerId);
        Log::debug('disconnect user');
        Log::debug($router->ip_address . '|' . $router->username . '|' . $router->password);
        Log::debug($user->username);

        $client = new RouterOS\Client($router->ip_address, $router->username, $router->password);
        $request = new RouterOS\Request('/ip hotspot active print');

        $query = RouterOS\Query::where('user', $user->username);

        $request->setQuery($query);
        $id = $client->sendSync($request)->getProperty('.id');

        $request = new RouterOS\Request('/ip hotspot active remove');
        $request->setArgument('numbers', $id);
        $responses = $client->sendSync($request);
        // Log::debug('disconnected');
        return;
    }

    public function update($id)
    {
        return $this->form($id)->update($id);
    }

    public function store()
    {
        return $this->form()->store();
    }

}
