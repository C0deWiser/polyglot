<?php

namespace Codewiser\Polyglot\Http\Middleware;

use Closure;
use Codewiser\Polyglot\Http\Translations;
use Codewiser\Polyglot\Polyglot;
use Illuminate\Http\Request;

class DetectLanguage
{
    use Translations;

    public function handle(Request $request, Closure $next)
    {
        $locales = $this->getLocales();

        $lang = $request->getPreferredLanguage($locales);

        if (!in_array($lang, $locales)) {
            $lang = 'en';
        }

        app()->setLocale($lang);

        return $next($request);
    }
}
