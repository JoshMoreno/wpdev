<?php
namespace WPDev;

use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/*
|--------------------------------------------------------------------------
| Initialize Whoops
|--------------------------------------------------------------------------
| Better and prettier error handling
|
*/
$whoops = new Run;
$whoops->pushHandler(new PrettyPageHandler)->register();
