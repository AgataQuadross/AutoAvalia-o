<?php

$env_file = parse_ini_file(__DIR__ . '/.env', true);
define('DB_HOST', $env_file['DB_HOST']);
define('DB_NAME', $env_file['DB_NAME']);
define('DB_USER', $env_file['DB_USER']);
define('DB_PASS', $env_file['DB_PASS']);
define('DB_CHARSET', 'utf8mb4');

?>