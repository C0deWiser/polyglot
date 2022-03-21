<?php

namespace Codewiser\Polyglot\Http\Middleware;

use Closure;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AcceptLanguage
{
    public function handle(Request $request, Closure $next)
    {
        $locales = Polyglot::getLocales();

        if (Arr::isAssoc($locales)) {
            $locales = array_keys($locales);
        }

        app()->setLocale($request->getPreferredLanguage($locales));

        return $next($request);
    }
}
