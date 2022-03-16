<?php

namespace App\Admin\Controllers;

use App\Model\Radius\Radacct;
use App\Model\Radius\Radcheck;
use App\Router;
use App\Service;
use App\Support\RadiusTrait;
use App\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;

class UserController extends Controller
{
    use ModelForm;
    use RadiusTrait;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        Admin::script($this->script());

        return Admin::content(function (Content $content) {

            $content->header('Hotspot Users');
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

            $content->header('Hotspot User');
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

            $content->header('Hotspot User');
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
        // Log::debug('masuk ke grid user controller');
        return Admin::grid(User::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name()->sortable();
            $grid->username()->sortable();
            $grid->password();
            $grid->service()->name('Service');
            $grid->status()->editable('select', [0 => 'Not Active', 1 => 'Active'])->sortable();
            $grid->column('Connection Status')->display(function () {
                // Log::debug($id);
                // return $id;
                // $id = $this->id;
                $username = User::find($this->id)->username;
                $onlineStatus = Radacct::where('username', $username)->where('acctstoptime', null)->first();
                if ($onlineStatus) {
                    $router = Router::where('ip_address', $onlineStatus->nasipaddress)->first();
                    // $status = 'Online at '.$router->name;
                    $status = 'Online';
                } else {
                    $status = 'Offline';
                }
                return $status;
            });
            $grid->actions(function ($actions) {

                // append an action.
                $actions->prepend('<a href="users/log/'.$actions->getKey().'"><i class="fa fa-calendar-o"></i></a>');

                // prepend an action.
                // $actions->prepend('<a href=""><i class="fa fa-paper-plane"></i></a>');
            });

            $grid->tools(function ($tools) {
                $tools->append('<button type="button" class="btn btn-primary" id="random-password" value="add">Change All Password</button>');

            });

            $grid->filter(function($filter){

                // Add a column filter
                $filter->like('name', 'Name');
                $filter->like('username', 'Username');

                // search on relation table
                /*$filter->where(function ($query) {

                    $query->whereHas('service', function ($query) {
                        $query->where('name', 'like', "%{$this->input}%");
                    });

                }, 'Service');*/

                $filter->equal('service_id', 'Service')->select(Service::all()->pluck('name', 'id'));
                $filter->equal('status')->select([0 => 'Not Active', 1 => 'Active']);

            });
        });
    }

    protected function form($id = null) {
        // Log::debug('form');
        return Admin::form(User::class, function (Form $form) use ($id) {
            $form->display('id', 'ID');
            $form->text('name', 'Name')->rules('required');
            $form->text('email', 'Email')->rules('sometimes|nullable|email');

            if ($id) {
                $form->display('username', 'Username')->rules('required|alpha_dash'.Rule::unique('tenant.users')->ignore($id));
            } else {
                $form->text('username', 'Username')->rules('required|alpha_dash|unique:tenant.users|unique:radius.radcheck');
            }

            $form->text('password', 'Password')->rules('required');
            $form->select('service_id', 'Service')->options(Service::all()->pluck('name', 'id'))->rules('required');
            $form->select('status', 'Status')->options([0 => 'Reject', 1 => 'Accept']);
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->saving(function (Form $form) use ($id) {
                // Log::debug('form nya: ' . $form);
                // dd($form);
                // Simpan data di tabel radius
                if ($id) {
                    $this->editUser($id, $form);
                    $this->editUserGroup($id, $form);
                } else {
                    $this->addUser($form);
                    $this->addUserGroup($form);
                }

                // if ($id) {
                //     $error = new MessageBag([
                //         'title'   => 'title...',
                //         'message' => 'edit....',
                //     ]);
                // } else {
                //     $error = new MessageBag([
                //         'title'   => 'title...',
                //         'message' => 'create....',
                //     ]);
                // }
                //
                // return back()->with(compact('error'));
            });
        });
    }

    public function update($id)
    {
        return $this->form($id)->update($id);
    }

    public function store()
    {
        return $this->form()->store();
    }

    public function destroy($id)
    {
        // Log::debug('delete user. ID=' . $id);

        $data = $this->deleteUser($id);
        if ($this->form()->destroy($data['valid_id'])) {
            if ($data['error_username']) {
                return response()->json([
                    'status'  => false,
                    'message' => $data['error_username'] . ' are still online' ,
                ]);
            }
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }

    public function create_random_password()
    {

        Log::debug('create random password');
        foreach (User::all() as $user) {
            $username = $user->username;
            $password = mt_rand(10000000, 99999999);

            // update room
            $user->password = $password;
            $user->save();

            // update radius password
            $this->editUserPassword($username, $password);
        }
        // Log::debug('delete user. ID=' . $id);

        // return response()->json([
        //     'status'  => true,
        //     'message' => 'All password changed',
        // ]);
    }

    public function script()
    {
        return <<<EOT

        $('#random-password').on('click', function() {
            toastr.info('Please wait until process is finish.');
            $.ajax({
                method: 'post',
                url: 'users/create_random_password',
                data: {
                    _token:LA.token,
                },
                success: function () {
                    $.pjax.reload('#pjax-container');
                    toastr.success('Successfully change all password');
                },
                error: function(xhr, textStatus, error) {
                    toastr.info('Error code UC01. Please refresh this page and try again');
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        });

EOT;

    }
}
