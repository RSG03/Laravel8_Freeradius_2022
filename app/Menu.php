<?php

namespace App;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use App\Admin\Traits\ModelTree as AppModelTree;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use ModelTree, AppModelTree, AdminBuilder {
        AppModelTree::allNodes insteadof ModelTree;
        AppModelTree::saveOrder insteadof ModelTree;
    }

    protected $table = 'admin_menu';
    protected $fillable = ['parent_id', 'order', 'title', 'icon', 'uri', 'client_can_view'];

}
