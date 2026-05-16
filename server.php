<?php
$host = '0.0.0.0';
$port = 5000;
$docroot = __DIR__ . '/public';

$cmd = "php -S {$host}:{$port} -t {$docroot}";
passthru($cmd);
