<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class CheckRow
{
    protected $userId;
    protected $routerId;

    public function __construct($payload)
    {
        $this->userId = $payload['userId'];
        $this->routerId = $payload['routerId'];
    }

    protected function script()
    {
        return <<<SCRIPT

$('.grid-check-row').on('click', function () {

    var userid = $(this).data('userid');
    var routerid = $(this).data('routerid');

    // Your code.
    // console.log($(this).data('userid'));
    // alert(userid);
    // alert(routerid);
    $.ajax({
            method: 'get',
            url: 'disconnect/' + userid + '/' + routerid,
            data: {
                _method:'delete',
                _token:LA.token,
            },
            success: function (data) {
                $.pjax.reload('#pjax-container');
                swal('User disconnected', '', 'success');
                if (typeof data === 'object') {
                    if (data.status) {
                        swal(data.message, '', 'success');
                    } else {
                        swal(data.message, '', 'error');
                    }
                }
            }
        });

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return "<a class='btn btn-xs btn-success fa fa-check grid-check-row' data-userid='{$this->userId}' data-routerid='{$this->routerId}'></a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}