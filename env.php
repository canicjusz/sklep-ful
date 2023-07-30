<?php
$env = parse_ini_file('.env');
foreach ($env as $env_variable => $env_value) {
  $_ENV[$env_variable] = $env_value;
}