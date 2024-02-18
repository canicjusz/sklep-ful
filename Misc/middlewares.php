<?php
// use Middleware\Auth;
use Core\Middleware;
use Middleware\{Redirect, ExtractData};

Middleware::add(
  'redirectCategory',
  [Redirect::class, 'category']
);

Middleware::add(
  'extractCatalogPageData',
  [ExtractData::class, 'catalog']
);
