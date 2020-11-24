<?php

namespace App\Http;

use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Http\Message\Status;

class HttpEvent
{
    public static function onRequest(Request $request, Response $response): bool
    {
        $allowOrigin = array(
            '*',
        );

        //设置本地IP
        $ipArr = [
            '0.0.0.0',
            '127.0.0.1',
        ];
        //获得来源IP
        $host = implode(':', $request->getHeader('host'));
        // var_dump($request->getHeaders());
        if (!in_array($host[0], $ipArr)) {
            $origin = $request->getHeader('origin');

            if (!empty($origin)) {
                $origin = $origin[0];
                if (in_array($origin, $allowOrigin) || in_array('*', $allowOrigin)) {
                    $response->withHeader('Access-Control-Allow-Origin', $origin);
                    $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
                    $response->withHeader('Access-Control-Allow-Credentials', 'true');
                    $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With,X_Requested_With, token');

                    if ($request->getMethod() === 'OPTIONS') {

                        $response->withStatus(Status::CODE_OK);
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
