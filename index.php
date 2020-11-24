<?php
 header('Content-Type: text/html;charset=utf-8');
 header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
 header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
 header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
 header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin'); // 设置允许自定义请求头的字段
 
if(isPost()) {
    if(!empty($_POST['f'])) {
        if(function_exists($_POST['f'])){
            return $_POST['f']();
        }
        return send_http_status(401);
    }
}

return send_http_status(404);



function restartShell() {
    if(statusShell() === true) {
        if(stopShell() === true) {
            if(startShell() === true) {
                die('true');
            }
        }
        die('false');
    }else{
        if(startShell() === true) {
            die('true');
        }

        die('false');
    }
}

 /**
 * Undocumented function
 * 服务进程开启
 * @return void
 */
function startShell(){
    $res = shell_exec(PHP_BINDIR.'/php easyswoole server start -d ');
    $res = str_replace('#!/usr/bin/env php','',$res);
    if(empty($res)) {
        return false;
    }
    return true;
}

/**
 * Undocumented function
 * 服务进程关闭
 * @return void
 */
function stopShell(){
    $res = shell_exec(PHP_BINDIR.'/php easyswoole server stop force');
    $res = str_replace('#!/usr/bin/env php','',$res);
    if(empty($res)) {
        return false;
    }
    return true;
}


/**
 * Undocumented function
 * 服务进程状态
 * @return void
 */
function statusShell(){
    $res = shell_exec(PHP_BINDIR.'/php easyswoole server status');
    $res = str_replace('#!/usr/bin/env php','',$res);
   
    if(empty($res) || strpos($res,'connect to server fail') !== false) {
        return false;
    }
    return true;
}

/**
 * 判断POST
 */
function isPost()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        return true;
    } else {
        return false;
    }
}


function send_http_status($code) {
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if (array_key_exists($code, $_status)) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
    }
}
