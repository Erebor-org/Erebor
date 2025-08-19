<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    file_put_contents(
        __DIR__.'/../var/log/debug_request.log',
        $request->getContent(),
        FILE_APPEND
    );
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};