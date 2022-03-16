<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/13/17
 * Time: 11:40 AM
 */

namespace App\Support;


use App\Model\Radius\Radacct;
use App\Model\Radius\Radcheck;
use App\Model\Radius\Radgroupreply;
use App\Model\Radius\Radusergroup;
use App\Room;
use App\Service;
use App\User;
use Illuminate\Support\Facades\Log;

trait RadiusTrait
{
    /**
     * Edit tabel radcheck
     *
     * @param $id
     * @param $form
     */
    public function editUser($id, $form) {
        $user = User::findOrFail($id);
        // $user = User::find($id);
        // Log::debug('form password :'.$form->password);

        // password is always required. Empty password exist when user use editable
        // to switch between active or in-active
        $passwordValue = $form->password;
        if (empty($passwordValue)) {
            $passwordValue = $user->password;
        }

        $radcheck = Radcheck::where('username', $user->username)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => $passwordValue]);

        if (!$radcheck && !empty($form->password)) {
            $this->addUser($form, $user);
            return;
        }

        Radcheck::where('username', $user->username)
            ->where('attribute', 'Auth-Type')
            ->update(['value' => ($form->status == 1) ? 'Accept' : 'Reject']);
    }

    public function editUserPassword($username, $password) {

        Radcheck::where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => $password]);
    }

    public function editRoom($id, $form) {
        $user = Room::findOrFail($id);
        // $user = User::find($id);

        // password is always required. Empty password exist when user use editable
        // to switch between active or in-active
        $passwordValue = $form->password;
        if (empty($passwordValue)) {
            $passwordValue = $user->password;
        }

        $radcheck = Radcheck::where('username', $user->username)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => $passwordValue]);

        if (!$radcheck && !empty($form->password)) {
            $this->addUser($form, $user);
            return;
        }

        Radcheck::where('username', $user->username)
            ->where('attribute', 'Auth-Type')
            ->update(['value' => ($form->status == 1) ? 'Accept' : 'Reject']);
    }

    public function editRoomPassword($username, $password) {

        Radcheck::where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->update(['value' => $password]);
    }

    /**
     * @param $form
     * @param null $user
     */
    public function addUser($form, $user = null) {
        $radcheck = new Radcheck();
        $radcheck->username = ($user) ? $user->username : $form->username;
        $radcheck->attribute = 'Cleartext-Password';
        $radcheck->op = ':=';
        $radcheck->value = $form->password;
        $radcheck->save();

        $radcheck = new Radcheck();
        $radcheck->username = ($user) ? $user->username : $form->username;
        $radcheck->attribute = 'Auth-Type';
        $radcheck->op = ':=';
        $radcheck->value = ($form->status == 1) ? 'Accept' : 'Reject';
        $radcheck->save();
    }

    public function deleteUser($id) {
        $ids = explode(',', $id);
        $valid_id = array();
        $error_username = array();

        foreach ($ids as $id) {
            if (empty($id)) {
                continue;
            }
            $user = User::find($id);
            $username = $user->username;
            $onlineStatus = Radacct::where('username', $username)->where('acctstoptime', null)->first();

            if ($onlineStatus) {
                $error_username[] = $username;
                continue;
            }

            if ($user) {
                Radcheck::where('username', $user->username)
                    ->where('attribute', 'Cleartext-Password')
                    ->delete();
                Radcheck::where('username', $user->username)
                    ->where('attribute', 'Auth-Type')
                    ->delete();
                Radusergroup::where('username', $user->username)
                    ->delete();
            }
            $valid_id[] = $id;
        }
        $data = array(
            'error_username' => implode(', ', $error_username),
            'valid_id' => implode(',', $valid_id),
        );
        return $data;
    }

    public function deleteRoom($id) {
        $ids = explode(',', $id);
        $valid_id = array();
        $error_username = array();

        foreach ($ids as $id) {
            if (empty($id)) {
                continue;
            }

            $user = Room::find($id);
            $username = $user->username;
            $onlineStatus = Radacct::where('username', $username)->where('acctstoptime', null)->first();

            if ($onlineStatus) {
                $error_username[] = $username;
                continue;
            }

            if ($user) {
                Radcheck::where('username', $user->username)
                    ->where('attribute', 'Cleartext-Password')
                    ->delete();
                Radcheck::where('username', $user->username)
                    ->where('attribute', 'Auth-Type')
                    ->delete();
                Radusergroup::where('username', $user->username)
                    ->delete();
            }
            $valid_id[] = $id;
        }

        $data = array(
            'error_username' => implode(', ', $error_username),
            'valid_id' => implode(',', $valid_id),
        );
        return $data;
    }

    /**
     * Edit tabel radusergroup
     *
     * @param $id
     * @param $form
     */
    public function editUserGroup($id, $form) {
        if (!$form->service_id) {
            // Log::debug('empty service_id');
            return;
        }
        $service = Service::find($form->service_id);
        $user = User::find($id);

        if ($user) {
            // Log::debug('user exist');

            Radusergroup::where('username', $user->username)
                ->delete();

            $radusergroup = new Radusergroup();
            $radusergroup->username = $user->username;
            $radusergroup->groupname = $form->service_id.config('radius.suffix_group_name').session('tenant');
            $radusergroup->priority = $service->priority;
            $radusergroup->save();
        }
    }

    public function editRoomGroup($id, $form) {
        if (!$form->service_id) {
            return;
        }
        $service = Service::find($form->service_id);
        $user = Room::find($id);

        if ($user) {
            Radusergroup::where('username', $user->username)
                ->delete();

            $radusergroup = new Radusergroup();
            $radusergroup->username = $user->username;
            $radusergroup->groupname = $form->service_id.config('radius.suffix_group_name').session('tenant');
            $radusergroup->priority = $service->priority;
            $radusergroup->save();
        }
    }

    public function addUserGroup($form) {
        // Log::debug('masuk ke addUserGroup, form service id='.$form->service_id);
        if (!$form->service_id) {
            // Log::debug('masuk ke return');
            return;
        }
        $service = Service::find($form->service_id);

        $radusergroup = new Radusergroup();
        $radusergroup->username = $form->username;
        $radusergroup->groupname = $form->service_id.config('radius.suffix_group_name').session('tenant');
        $radusergroup->priority = $service->priority;
        $radusergroup->save();
    }

    public function addGroup($id, $form) {
        $radgroupreply = new Radgroupreply();
        $radgroupreply->value = $form->bandwidthup;
        $radgroupreply->groupname = $id.config('radius.suffix_group_name').session('tenant');
        $radgroupreply->attribute = 'Ascend-Data-Rate';
        $radgroupreply->op = ':=';
        $radgroupreply->save();

        $radgroupreply = new Radgroupreply();
        $radgroupreply->value = $form->bandwidthdown;
        $radgroupreply->groupname = $id.config('radius.suffix_group_name').session('tenant');
        $radgroupreply->attribute = 'Ascend-Xmit-Rate';
        $radgroupreply->op = ':=';
        $radgroupreply->save();
    }

    public function editGroup($id, $form) {
        Radgroupreply::where('groupname', $id.config('radius.suffix_group_name').session('tenant'))
            ->where('attribute', 'Ascend-Data-Rate')
            ->update(['value' => $form->bandwidthup]);

        Radgroupreply::where('groupname', $id.config('radius.suffix_group_name').session('tenant'))
            ->where('attribute', 'Ascend-Xmit-Rate')
            ->update(['value' => $form->bandwidthdown]);

        Radusergroup::where('groupname', $id.config('radius.suffix_group_name').session('tenant'))
            ->update(['priority' => $form->priority]);
    }

    public function deleteGroup($id) {
        Radgroupreply::where('groupname', $id.config('radius.suffix_group_name').session('tenant'))
            ->delete();
    }

}