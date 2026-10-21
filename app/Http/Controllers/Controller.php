<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// Extends Illuminate\Routing\Controller (laravel/ui's base controller) so the
// laravel/ui auth controllers and HomeController can keep registering their
// middleware with $this->middleware() in the constructor.
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
