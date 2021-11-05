<?php

namespace App\Providers;

use Codewiser\Polyglot\PolyglotApplicationServiceProvider;
use Illuminate\Support\Facades\Gate;

class PolyglotServiceProvider extends PolyglotApplicationServiceProvider
{
    /**
     * Register the Polyglot gate.
     *
     * This gate determines who can access Polyglot in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewPolyglot', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}