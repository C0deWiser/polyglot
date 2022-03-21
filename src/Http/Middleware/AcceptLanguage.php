<?php

namespace Codewiser\Polyglot\Http\Middleware;

use Closure;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;

class AcceptLanguage
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale($request->getPreferredLanguage(Polyglot::getLocales()));

        return $next($request);
    }
}
