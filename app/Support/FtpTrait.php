<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

trait FtpTrait
{

    public function ftp_rdel($handle, $path) {
        Log::debug('masuk ftp_rdel, path: '.$path);
        if (@ftp_delete($handle, $path) === false) {
            Log::debug('masuk ftp_delete');

            if ($children = @ftp_nlist($handle, $path)) {
                Log::debug('masuk ftp_nlist');

                foreach ($children as $p) {
                    Log::debug('foreach p: ' . $p);
                    if ($p != '.' && $p != '..') {
                        $this->ftp_rdel($handle, $path.'/'.$p);
                    }
                }
            }

            @ftp_rmdir($handle, $path);
        }
    }

    function ftp_rmdirr($handle, $path)
    {
        if(!@ftp_delete($handle, $path))
        {
            $list = @ftp_nlist($handle, $path);
            if(!empty($list))
                foreach($list as $value)
                    $this->ftp_rmdirr($handle, $path.'/'.$value);
        }

        @ftp_rmdir($handle, $path);
    }

    function recursiveDelete($handle, $directory)
    {
        Log::debug('recursive delete directory: ' . $directory);
        // # here we attempt to delete the file/directory
        if (!(@ftp_rmdir($handle, $directory) || @ftp_delete($handle, $directory))) {
            Log::debug('masuk ke if');
            // # if the attempt to delete fails, get the file listing
            $filelist = @ftp_nlist($handle, $directory);

            // # loop through the file list and recursively delete the FILE in the list
            foreach ($filelist as $file) {
                Log::debug('file: ' . $file);
                if ($file != '.' && $file != '..') {
                    $this->recursiveDelete($handle, $directory.$file);
                }
            }

            // #if the file list is empty, delete the DIRECTORY we passed
            $this->recursiveDelete($handle, $directory);
        }
    }

    public function ftp_putAll($conn_id, $src_dir, $dst_dir) {
        $d = dir($src_dir);
        while($file = $d->read()) { // do this for each file in the directory
            if ($file != "." && $file != "..") { // to prevent an infinite loop
                if (is_dir($src_dir."/".$file)) { // do the following if it is a directory
                    if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {
                        ftp_mkdir($conn_id, $dst_dir."/".$file); // create directories that do not yet exist
                    }
                    $this->ftp_putAll($conn_id, $src_dir."/".$file, $dst_dir."/".$file); // recursive part
                } else {
                    $upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY); // put the files
                }
            }
        }
        $d->close();
    }

}