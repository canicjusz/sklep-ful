<?php

use Core\Middleware;

Middleware::add(
  'auth',
  [Auth::class, 'index']
);