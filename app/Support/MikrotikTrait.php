<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use PEAR2\Net\RouterOS;

trait MikrotikTrait
{

    public function disconnectUser($user, $ip, $option = array('id' => 'admin', 'pass' => 'dycode'))
    {
        $client = new RouterOS\Client($ip, $option['id'], $option['pass']);
        $request = new RouterOS\Request('/ip hotspot active print');

        $query = RouterOS\Query::where('user', $user);

        $request->setQuery($query);
        $id = $client->sendSync($request)->getProperty('.id');

        $request = new RouterOS\Request('/ip hotspot active remove');
        $request->setArgument('numbers', $id);
        $responses = $client->sendSync($request);
    }

    public function changeLandingPage($ip = '202.150.158.238', $option = array('id' => 'admin', 'pass' => 'dycode'))
    {
        $client = new RouterOS\Client($ip, $option['id'], $option['pass']);
        $request = new RouterOS\Request('/ip hotspot profile set hsprof1 html-directory=template');
        $responses = $client->sendSync($request);
        // Log::debug($responses);

        // /ip hotspot profile set (profile number or name) html-directory-override=(dir path/name)
    }

    public function removeTemplate($ip = '202.150.158.238', $option = array('id' => 'admin', 'pass' => 'dycode'))
    {
        $client = new RouterOS\Client($ip, $option['id'], $option['pass']);
        $request = new RouterOS\Request('/file remove template');
        $client->sendSync($request);
        // Log::debug($responses);
    }

}