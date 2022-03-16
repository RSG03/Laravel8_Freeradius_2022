<?php

namespace App\Http\Controllers;

use Anchu\Ftp\Facades\Ftp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class MyTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index__()
    {
        Config::set('ftp.connections.wbj', array(
            'host'   => '103.24.150.18',
            'username' => 'admin',
            'password'   => 'dycode',
            'passive'   => false,
            'secure'   => false,
        ));

        // $listing = FTP::connection('wbj')->changeDir('/flash/hotspot/');
        // $listing = FTP::connection('wbj')->currentDir();
        // $listing = FTP::connection('wbj')->getDirListing('/flash/hotspot');
        // Log::debug($listing);
        $listing = FTP::connection('wbj')->downloadFile('/flash/hotspot/img/3.png', storage_path('app/public'));
        Log::debug($listing);
        // Log::debug(storage_path('app/public'));
        return view('home');
    }

    public function index()
    {
        $ftp_server = '103.24.150.18';
        $ftp_user_name = 'admin';
        $ftp_user_pass = 'dycode';
        $conn_id = ftp_connect($ftp_server);
        $remote_file = '/flash/hotspot/myftp.html';
        $file = storage_path('app/public/myftp.html');

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
        if (ftp_put($conn_id, $remote_file, $file, FTP_ASCII)) {
            Log::debug("successfully uploaded $file\n");
        } else {
            Log::debug("There was a problem while uploading $file\n");
        }

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
    }
}
