<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use App\Http\Controllers\ClientDashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Dashboard');

            if (session('tenant')) {
                $content->row(ClientDashboard::info_box());
            } else {

                $content->row(function (Row $row) {

                    $row->column(4, function (Column $column) {
                        $column->append(Dashboard::environment());
                    });

                    $row->column(4, function (Column $column) {
                        $column->append(Dashboard::extensions());
                    });

                    $row->column(4, function (Column $column) {
                        $column->append(Dashboard::dependencies());
                    });
                });
            }
        });
    }

    public function error_page()
    {
        if(Session::get('xenos-success') || Session::get('xenos-error') || Session::get('xenos-warning') || Session::get('xenos-info')) {
            return Admin::content(function (Content $content) {});
        } else {
            return Admin::content(function (Content $content) {
                $content->body(view('blank_error'));
            });
        }
    }
}
