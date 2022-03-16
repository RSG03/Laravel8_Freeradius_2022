<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class RoomAccept extends BatchAction
{
    protected $action;
    protected $action_text;

    public function __construct($action = 1)
    {
        $this->action = $action;
        if ($action == 1) {
            $this->action_text = 'ACCEPT';
        } else {
            $this->action_text = 'REJECT';
        }
    }

    public function script()
    {
        return <<<EOT

$('{$this->getElementClass()}').on('click', function() {

    $.ajax({
        method: 'post',
        url: '{$this->resource}/set_status',
        data: {
            _token:LA.token,
            ids: selectedRows(),
            action: {$this->action}
        },
        success: function () {
            $.pjax.reload('#pjax-container');
            toastr.success('Successfully set to {$this->action_text}');
        }
    });
});

EOT;

    }
}