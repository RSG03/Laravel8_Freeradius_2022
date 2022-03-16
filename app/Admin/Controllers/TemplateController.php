<?php

namespace App\Admin\Controllers;

use App\Support\FtpTrait;
use App\Support\MikrotikTrait;
use App\Template;

use Chumper\Zipper\Zipper;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

use PEAR2\Net\RouterOS;


class TemplateController extends Controller
{
    use ModelForm, MikrotikTrait, FtpTrait;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Template');
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

            $content->header('Template');
            $content->description('Edit');

            $content->body($this->form()->edit($id));
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

            $content->header('Template');
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
        return Admin::grid(Template::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->name();
            $grid->cover()->display(function ($cover) {
                return '<img src="'.asset('/uploads/'.$cover).'" alt="" width="50px" height="50px">';

            });
            $grid->status()->display(function ($status) {
                return ($status == 1) ? "Active" : "Not active";
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Template::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name')->rules('required');
            $form->textarea('description')->rows(3);
            $form->image('cover', 'Thumb Image');
            $form->file('filename', 'Zip files');
            $form->select('status', 'Status')->options([0 => 'Not active', 1 => 'Active']);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->saved(function (Form $form) {
                $id = $form->model()->id;

                if ($form->status == 1) {

                    // delete extract folder & re-create folder
                    File::cleanDirectory(public_path('/uploads/files/extract'));

                    // extract zip
                    $zipper = new Zipper();
                    $zipper->make(public_path('/uploads/' . $form->model()->filename))->extractTo(public_path('/uploads/files/extract'));
                    $zipper->close();

                    // upload to ftp
                    $ftp_server = '202.150.158.238';
                    $ftp_user_name = 'admin';
                    $ftp_user_pass = 'dycode';
                    $conn_id = ftp_connect($ftp_server);
                    // $remote_file = '/hotspot/myftp.html';
                    // $file = storage_path('app/public/myftp.html');

                    // login with username and password
                    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
                    ftp_pasv($conn_id, true);
                    // check connection
                    if ((!$conn_id) || (!$login_result)) {
                        Log::debug("FTP connection has failed!");
                        Log::debug("Attempted to connect to $ftp_server for user $ftp_user_name");
                        exit;
                    } else {
                        Log::debug("Connected to $ftp_server, for user $ftp_user_name");
                    }
                    // $buff = ftp_rawlist($conn_id, '.');
                    // var_dump($buff);

                    // upload a file
                    // if (ftp_put($conn_id, $remote_file, $file, FTP_ASCII)) {
                    //     Log::debug("successfully uploaded $file\n");
                    // } else {
                    //     Log::debug("There was a problem while uploading $file\n");
                    // }


                    // delete previous content
                    $this->recursiveDelete($conn_id, 'template/');
                    // ftp_rmdir($conn_id, 'template');
                    // $this->ftp_rdel($conn_id, 'template');
                    // $this->ftp_rmdirr($conn_id, 'template');
                    // $this->removeTemplate();

                    // $client = new RouterOS\Client('202.150.158.238', 'admin', 'dycode');
                    // // Log::debug('client routeros: ' . $client);
                    // // dd($client);
                    // $request = new RouterOS\Request('/file remove template');
                    // $client->sendSync($request);

                    // re-create directory
                    ftp_mkdir($conn_id, 'template');


                    // upload directory
                    // foreach (glob(public_path('/uploads/files/extract')."/*\.*") as $filename)
                    //     ftp_put($conn_id, '/template/'.basename($filename) , $filename, FTP_BINARY);
                    $this->ftp_putAll($conn_id, public_path('/uploads/files/extract'), 'template');


                    // upload the file
                    // $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

                    // check upload status
                    // if (!$upload) {
                    //     echo "FTP upload has failed!";
                    // } else {
                    //     echo "Uploaded $source_file to $ftp_server as $destination_file";
                    // }

                    // close the FTP stream
                    ftp_close($conn_id);

                    // $this->changeLandingPage();

                    // Aktifkan id ini
                    // $zip = new ZipArchive();
                    // // $statuszip = $zip->open(asset('/uploads/' . $form->model()->filename));
                    // $statuszip = $zip->open(public_path('/uploads/' . $form->model()->filename));
                    // if (is_resource($statuszip)) {
                    //     // consider zip file opened successfully
                    //     Log::debug('ok');
                    // }
                    // $zip->extractTo(asset('uploads/files'));
                    // $zip->close();
                    // Log::debug('file asset: ' . asset('/uploads/' . $form->model()->filename));
                    // Log::debug('file public path: ' . public_path('/uploads/' . $form->model()->filename));
                    // Log::debug('status zip :'.$statuszip);

                    // --------------------------------------

                    // if ($zip->open(asset('/uploads/'.$form->model()->filename))) {
                    //     $zip->extractTo(asset('uploads/files'));
                    //     $zip->close();
                    //     Log::debug('ok');
                    // } else {
                    //     Log::debug('extract gagal');
                    //     Log::debug($form->model()->filename);
                    //     Log::debug(asset('/uploads/' . $form->model()->filename));
                    //
                    // }



                    // clean extract folder
                    File::cleanDirectory(public_path('/uploads/files/extract'));


                    // Non aktifkan id yang lain
                    Template::where('id', '<>', $id)
                        ->where('status', 1)
                        ->update(['status' => 0]);
                }
            });
        });
    }

}
