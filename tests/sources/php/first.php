<?php

use Illuminate\Support\Facades\Lang;

__('Short message');

Lang::get('Short message');
Illuminate\Support\Facades\Lang::get('Short message');

/* Testing context */
pgettext('Test context', 'Short message');

/* Testing domain change */
dgettext('shell', 'Message for shell');

ngettext('One cat', 'Few cats', rand(0, 10));

npgettext('Test context', 'One cat', 'Few cats', rand(0, 10));

trans_choice('short.plural', rand(0, 10));
trans_choice('One lamp|Few lamps', rand(0, 10));

__('short.message');