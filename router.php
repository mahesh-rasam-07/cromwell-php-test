<?php
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($uri === '/user/registration') { require 'web/user/registration.php'; return; }
    if ($uri === '/user/login')        { require 'web/user/login.php'; return; }
    if ($uri === '/api/user')          { require 'api/user.php'; return; }
    if ($uri === '/api/login')         { require 'api/login.php'; return; }

    http_response_code(404);
    echo '404 Not Found';