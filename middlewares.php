<?php
// use Middleware\Auth;
use Core\Middleware;

Middleware::add(
  'redirectCategory',
  [Redirect::class, 'category']
);