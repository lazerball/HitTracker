<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
    || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file.');
}

$loader = require_once __DIR__.'/../app/autoload.php';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

if (function_exists('register_postsend_function')) {
    register_postsend_function(function () use ($kernel, $request, $response) {
        $kernel->terminate($request, $response);
    });
} else {
    $kernel->terminate($request, $response);
}
