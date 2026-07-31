<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__).'/vendor/autoload.php';

$envFile = dirname(__DIR__).'/.env';

if (is_file($envFile)) {
    (new Dotenv())->bootEnv($envFile);
}

if ($_SERVER['APP_DEBUG'] ?? false) {
    umask(0000);
}

if (false === (bool) ($_SERVER['APP_DEBUG'] ?? false)) {
    (new Filesystem())->remove(dirname(__DIR__).'/var/cache/test');
}
