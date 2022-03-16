<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class DisconnectUser
{
    protected $userId;
    protected $routerId;
    protected $model;

    public function __construct($payload)
    {
        $this->userId = $payload['userId'];
        $this->routerId = $payload['routerId'];
        $this->model = $payload['model'];
    }

    protected function script()
    {
        return <<<SCRIPT

$('.grid-delete-row').on('click', function () {

    var userid = $(this).data('userid');
    var routerid = $(this).data('routerid');
    var model = $(this).data('model');

    $.ajax({
            method: 'get',
            url: 'disconnect/' + userid + '/' + routerid  + '/' + model,

            success: function (data) {
                $.pjax.reload('#pjax-container');
                swal('User disconnected', '', 'success');
               
            }
        });

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return "<a class='btn btn-xs fa fa-toggle-on grid-delete-row' data-userid='{$this->userId}' data-routerid='{$this->routerId}' data-model='{$this->model}'></a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}