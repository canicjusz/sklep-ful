<?php

require 'autoloader.php';
require 'debuggers.php';
require 'env.php';
require 'helpers.php';
require 'routes.php';
require 'middlewares.php';

use Core\{Route, Database};

Database::createInstance('localhost', 'root', '', 'sklep-ful');

Route::resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);