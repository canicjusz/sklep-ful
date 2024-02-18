<?php

define("ROOT_PATH", __DIR__);

require 'Helpers/index.php';
require 'Core/autoloader.php';
require 'Misc/env.php';
require 'Misc/routes.php';
require 'Misc/middlewares.php';

use Core\{Route, Database};

Database::createInstance('localhost', 'root', '', 'sklep-ful');

Route::resolve($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
