<?php

namespace Codewiser\Polyglot\Http\Controllers;

use Codewiser\Polyglot\Http\Middleware\Authorize;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(Authorize::class);
    }
}
