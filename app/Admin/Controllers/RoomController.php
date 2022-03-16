<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\RoomAccept;
use App\Company;
use App\Model\Radius\Radacct;
use App\Model\Radius\Radcheck;
use App\Room;
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

class RoomController extends Controller
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

            $content->header('Rooms');
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

            $content->header('Room');
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

            $content->header('Rooms');
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
        return Admin::grid(Room::class, function (Grid $grid) {
            $grid->model()->orderBy('name', 'asc');

            $grid->id('ID')->sortable();
            $grid->name('Room')->sortable();
            $grid->username()->sortable();
            $grid->password();
            $grid->guest_name('Guest')->sortable();
            $grid->check_out('Check-out')->sortable();
            $grid->service()->name('Service');
            $grid->status()->editable('select', [0 => 'Not Active', 1 => 'Active'])->sortable();
            // $grid->status();
            $grid->column('Connection Status')->display(function () {
                // Log::debug($id);
                // return $id;
                // $id = $this->id;
                $username = Room::find($this->id)->username;
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

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->add('Accept room login', new RoomAccept(1));
                    $batch->add('Reject room login', new RoomAccept(0));
                });
            });

            $grid->tools(function ($tools) {
                $tools->append('<button type="button" class="btn btn-primary" id="random-password" value="add">Change All Password</button>');

            });

            $grid->actions(function ($actions) {

                // append an action.
                $actions->prepend('<a href="rooms/log/'.$actions->getKey().'"><i class="fa fa-calendar-o"></i></a>');

                // prepend an action.
                // $actions->prepend('<a href=""><i class="fa fa-paper-plane"></i></a>');
            });

            $grid->filter(function($filter){

                // Add a column filter
                $filter->like('name', 'Room Name');
                $filter->like('username', 'Username');

                $filter->equal('service_id', 'Service')->select(Service::all()->pluck('name', 'id'));
                $filter->equal('status')->select([0 => 'Not Active', 1 => 'Active']);

            });
        });
    }

    protected function form($id = null) {
        if ($id) {
            return $this->form_edit($id);
        } else {
            return $this->form_create();
        }
    }

    protected function form_edit($id) {
        // Log::debug('form');
        return Admin::form(Room::class, function (Form $form) use ($id) {
            $form->display('id', 'ID');
            $form->text('name', 'Room Name')->rules('required');
            // $form->text('email', 'Email')->rules('sometimes|nullable|email');

            $form->display('username', 'Username')->rules('required|alpha_dash'.Rule::unique('tenant.users')->ignore($id));

            $form->text('password', 'Password')->rules('required');
            $form->text('guest_name', 'Guest Name');
            $form->datetime('check_in', 'Check-in');
            $form->datetime('check_out', 'Check-out');
            $form->select('service_id', 'Service')->options(Service::all()->pluck('name', 'id'))->rules('required');
            $form->select('status', 'Status')->options([0 => 'Reject', 1 => 'Accept']);
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->saving(function (Form $form) use ($id) {
                $this->editRoom($id, $form);
                $this->editRoomGroup($id, $form);
            });
        });
    }

    protected function form_create($id = null) {
        // Log::debug('form');
        return Admin::form(Room::class, function (Form $form) use ($id) {
            $form->display('id', 'ID');
            $form->text('number_start', 'Room Number Start')->rules('required|numeric');
            $form->text('number_end', 'Room Number End')->rules('required|numeric');
            $form->select('service_id', 'Service')->options(Service::all()->pluck('name', 'id'))->rules('required');
            $form->select('status', 'Status')->options([0 => 'Reject', 1 => 'Accept']);
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->hidden('name');
            $form->hidden('username');
            $form->hidden('password');
            // $form->ignore(['number_start', 'number_end']);
            $form->saving(function (Form $form) use ($id) {
                $error = '';
                $number_start = $form->number_start;
                $number_end = $form->number_end;
                $diff = $number_end - $number_start;

                $company = Company::find(session('tenant'));
                $room_prefix = $company->room_prefix;

                $password_array = config('radius.password_array');

                if ($diff > 50) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => 'Maximum 50 batch room number',
                    ]);
                } else if ($diff < 0) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => 'Room number end can not exceed room number start',
                    ]);
                }

                if ($error) {
                    return back()->with(compact('error'))->withInput();
                }


                if ($diff > 0) {
                    for ($x = $number_start; $x < $number_end; $x++) {
                        // Log::debug('room number: '.$x);
                        $form->name = 'Room ' . $x;
                        $form->username = $room_prefix . $x;
                        $k = array_rand($password_array);
                        $form->password = $password_array[$k];

                        // When duplicated data present then skip it
                        $q_room = Room::where('username', $form->username);
                        $get_room = $q_room->first();
                        if ($get_room) {
                            continue;
                        } else {
                            $room = new Room();
                            $room->name = $form->name;
                            $room->username = $form->username;
                            $room->password = $form->password;
                            $room->service_id = $form->service_id;
                            $room->status = $form->status;
                            $room->save();

                            // save to radius table
                            $this->addUser($form);
                            $this->addUserGroup($form);
                        }

                    }
                }
                // $form->ignore(['password_confirmation']);
                // unset($form->number_start);
                // unset($form->number_end);
                // $form->number_start = null;
                // $form->number_end = null;

                $form->name = 'Room ' . $number_end;
                $form->username = $room_prefix . $number_end;
                $k = array_rand($password_array);
                $form->password = $password_array[$k];

                // check this username for duplication
                $q_room = Room::where('username', $form->username);
                $get_room = $q_room->first();
                if ($get_room) {
                    $error = new MessageBag([
                        'title'   => 'Error',
                        'message' => $form->name . ' already exist! Existed room number data will not be changed on this batch processes',
                    ]);

                    return back()->with(compact('error'))->withInput();
                } else {
                    // save to radius table
                    $this->addUser($form);
                    $this->addUserGroup($form);
                }
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

        $data = $this->deleteRoom($id);
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

    public function set_status(Request $request)
    {
        foreach (Room::find($request->get('ids')) as $room) {
            $room->status = $request->get('action');
            $room->save();
            // Log::debug('set status id:');
            // Log::debug($room->id);

            Radcheck::where('username', $room->username)
                ->where('attribute', 'Auth-Type')
                ->update(['value' => ($room->status == 1) ? 'Accept' : 'Reject']);
        }
    }

    public function create_random_password()
    {

        Log::debug('create random password');
        foreach (Room::all() as $room) {
            $username = $room->username;
            $password = mt_rand(10000000, 99999999);

            // update room
            $room->password = $password;
            $room->save();

            // update radius password
            $this->editRoomPassword($username, $password);
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
                url: 'rooms/create_random_password',
                data: {
                    _token:LA.token,
                },
                success: function () {
                    $.pjax.reload('#pjax-container');
                    toastr.success('Successfully change all password');
                },
                error: function(xhr, textStatus, error) {
                    toastr.info('Error code RC01. Please refresh this page and try again');
                    console.log(xhr.statusText);
                    console.log(textStatus);
                    console.log(error);
                }
            });
        });
EOT;

    }
}
