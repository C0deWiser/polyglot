<?php

namespace Codewiser\Polyglot\Http\Middleware;

use Codewiser\Polyglot\Polyglot;
use Illuminate\Support\Facades\Gate;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return
            app()->environment('local') ||
            Gate::check('viewPolyglot', [$request->user()]) ?
                $next($request) :
                abort(403);
    }
}
